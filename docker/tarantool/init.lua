box.cfg{listen = 3301}

-- if box.space.messages then
--     box.space.messages:drop()
-- end

-- Создание space для сообщений
box.schema.space.create('messages', {if_not_exists = true})
box.space.messages:create_index('primary', {type = 'hash', parts = {1, 'unsigned'}, if_not_exists = true})
box.space.messages:create_index('dialog_id', {type = 'tree', parts = {2, 'unsigned'}, if_not_exists = true, unique = false})
box.space.messages:create_index('sender_id', {type = 'tree', parts = {3, 'string'}, if_not_exists = true, unique = false})
box.space.messages:create_index('dialog_id_created_at', {type = 'tree', parts = {2, 'unsigned', 6, 'unsigned'}, if_not_exists = true, unique = false})

-- Функция отправки сообщения
function send_message(message_id, dialog_id, sender_id, participant1_id, text)
    box.space.messages:insert{message_id, dialog_id, sender_id, participant1_id, text, os.time()}
    return {success = true, message = 'Message sent'}
end

-- Функция получения 100 последних сообщений по dialog_id
function list_messages(dialog_id)
    local messages = box.space.messages.index.dialog_id_created_at:select({dialog_id}, {limit = 100, iterator = 'EQ'})
    local result = {}
    for _, msg in pairs(messages) do
        table.insert(result, {
            message_id = msg[1],
            dialog_id = msg[2],
            sender_id = msg[3],
            participant1_id = msg[4],
            text = msg[5],
            created_at = msg[6]
        })
    end
    table.sort(result, function(a, b) return a.created_at > b.created_at end)
    return result
end

function insert_many(messages)
    for _, msg in ipairs(messages) do
        -- Проверка, что msg - это кортеж (массив)
        if type(msg) ~= 'table' then
            error('Expected table for message, got ' .. type(msg))
        end
        -- Проверка типов полей
        if type(msg[1]) ~= 'number' or msg[1] < 0 then
            error('Expected unsigned for message_id, got ' .. type(msg[1]))
        end
        if type(msg[2]) ~= 'number' or msg[2] < 0 then
            error('Expected unsigned for dialog_id, got ' .. type(msg[2]))
        end
        if type(msg[3]) ~= 'string' then
            error('Expected string for sender_id, got ' .. type(msg[3]))
        end
        if type(msg[4]) ~= 'string' then
            error('Expected string for participant1_id, got ' .. type(msg[4]))
        end
        if type(msg[5]) ~= 'string' then
            error('Expected string for text, got ' .. type(msg[5]))
        end
        if type(msg[6]) ~= 'number' or msg[6] < 0 then
            error('Expected unsigned for created_at, got ' .. type(msg[6]))
        end
        box.space.messages:insert(msg)
    end
    return {success = true}
end

function cleanup_old_messages()
    local dialog_ids = {}
    -- Собираем все уникальные dialog_id
    for _, msg in box.space.messages.index.dialog_id:pairs() do
        dialog_ids[msg[2]] = true
    end

    local deleted_count = 0
    for dialog_id, _ in pairs(dialog_ids) do
        -- Получаем все сообщения для диалога, отсортированные по created_at (возрастание)
        local messages = box.space.messages.index.dialog_id_created_at:select({dialog_id}, {iterator = 'GE'})
        if #messages > 100 then
            -- Удаляем сообщения, начиная с самых старых (первые в списке)
            for i = 1, #messages - 100 do
                box.space.messages:delete(messages[i][1])
                deleted_count = deleted_count + 1
            end
        end
    end
    return {success = true, deleted = deleted_count}
end
