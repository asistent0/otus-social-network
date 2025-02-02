# Наполнение данными

Для наполнения Базы реальными данными использовалась библиотека faker для php.
Данные похожи на реальные русские имена и города.

# Индекс

Был добавлен индекс

```sql
CREATE INDEX handle_user_name ON "user" (LOWER(first_name) varchar_pattern_ops, LOWER(last_name) varchar_pattern_ops)
```

```sql
EXPLAIN ANALYZE SELECT * FROM "user"
         WHERE LOWER(first_name) LIKE 'Анн%' AND LOWER(last_name) LIKE 'Петро%'
         ORDER BY id;
```

```log
Sort  (cost=200.51..200.57 rows=25 width=268) (actual time=0.021..0.021 rows=0 loops=1)
  Sort Key: id
  Sort Method: quicksort  Memory: 25kB
"  ->  Index Scan using handle_user_name on ""user""  (cost=0.42..199.93 rows=25 width=268) (actual time=0.017..0.017 rows=0 loops=1)"
        Index Cond: ((lower((first_name)::text) ~>=~ 'Анн'::text) AND (lower((first_name)::text) ~<~ 'Ано'::text) AND (lower((last_name)::text) ~>=~ 'Петро'::text) AND (lower((last_name)::text) ~<~ 'Петрп'::text))
        Filter: ((lower((first_name)::text) ~~ 'Анн%'::text) AND (lower((last_name)::text) ~~ 'Петро%'::text))
Planning Time: 0.086 ms
Execution Time: 0.037 ms
```

Пытался добавить обычный индекс, типа такого:

```sql
CREATE INDEX handle_user_name ON "user" (LOWER(first_name), LOWER(last_name))
```

Но он не работал. Потом нашел статью с таким предложением:

```log
Из-за сложности и многообразия locale в постгресе запрещено использовать индекс
для запросов вида LIKE 'что%' для всех locale кроме 'C'.
```

Поэтому добавил индекс с добавлением `varchar_pattern_ops`. И он заработал.

Индекс по двум полям, что бы происходил сначала отсев значений по первому полю, а потом по второму.

# Результаты


| Users        | Avg Latency (ms) | Throughput (req/sec) | Errors % |
| ------------ |:----------------:|:--------------------:|:--------:|
| Before Index |                  |                      |          |
| 1            |       3480       |         0.28         |    0     |
| 10           |       8711       |         1.1          |    0     |
| 100          |      51379       |         1.4          |   1.37   |
| 1000         |      56575       |         2.3          |  86.81   |
| After Index  |                  |                      |          |
| 1            |       2425       |         0.41         |    0     |
| 10           |       2866       |         3.4          |    0     |
| 100          |      14958       |         2.3          |    0     |
| 1000         |      54716       |         9.6          |  81.52   |

Видно, что после добавления индекса запросы стали работать быстрее.

На 1000 было сложно сделать, jMeter периодически падал, поэтому не уверен в правильности
результатов на этом показателе. Да и количество ошибок слишком много тут.

На 100, после индекса не было ни одной ошибки, а до они были.
