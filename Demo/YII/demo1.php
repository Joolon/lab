<?php

/**
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2018/9/19
 * Time: 19:27
 */

// DAO(Data Access Object) 数据访问对
// 查询构建器 的使用

// 主要的好处是：
//
// 1, 允许程序化建立一个复杂的SQL表达式
// 2，自动引用表明和列名来 防止跟SQL保留关键字以及特殊字符的冲突
// 3，在可以的情况下引用参数值，使用参数绑定，从而降低了SQL 注入攻击的风险。

$status = isset($_POST['status']) ? $_POST['status'] : null;


// 联合方式 UNION / UNION ALL
$subQuery_1 = (new Query())->select("id")->from("order");
$subQuery_2 = (new Query())->select("id")->from("order");

$subQuery_1->union($subQuery_2, true);// 第二个参数设置 true 则使用 UNION ALL

$subQuerySql = $subQuery_1->createCommand()->getRawSql();// 输出SQL
$rawSql = $subQuery_1->createCommand()->rawSql;// SQL语句  已绑定查询参数 与getRawSql()一样
$sql = $subQuery_1->createCommand()->sql;// SQL语句  参数使用占位符代替
$params = $subQuery_1->createCommand()->params;// 获取绑定的参数

$results = $subQuery_1->all();// 执行查询

// 使用子查询（必须是Query查询对象） 其结果是： SELECT id FROM ($subQuerySql) AS tmp_table;
$subSql1 = (new Query())->select('id')->from(['tmp_table' => $subQuery_1]);// tmp_table 是别名
$subSql2 = (new Query())->select('id')->from('user')->where(['=', 'username', '张三'])->one();

$query = (new Query())->select('id,username')
    ->from('tableName')
    ->where('1=1');
$query->addSelect('age');// 追加查询字段 SELECT id,username,age
$query->addSelect(['address,phone']);// SELECT id,username,address,phone


// JOIN => LEFT / RIGHT / INNER
$query->join('LEFT JOIN', 'order', 'order.user_id = tableName.id');
$query->leftJoin('order', 'order.user_id = tableName.id');
$query->leftJoin(['orderList' => $subQuery_2], 'orderList.user_id = tableName.id');// LEFT JOIN ($subQuery_2) AS orderList ON orderList.user_id = tableName.id


// AND
$query->andWhere(['in', 'sub_id', $subSql1]);// AND sub_id IN ($subSql1)
$query->andWhere(['sub_id', $subSql1]);// AND sub_id IN ($subSql1)
$query->andWhere(['=', 'user_id', $subSql2]);// AND user_id = $subSql2

// AND OR
$query->andWhere(['and', 'id=1', 'id=3']);// AND (id=1 AND id=3)
$query->andWhere(['or', 'id=1', 'id=3']);// AND (id=1 or id=3)
$query->andWhere(['and', 'id=1', ['or', 'id=2', 'id=3']]);// AND id=1 AND (id=2 OR id=3)

// OR AND
$query->orWhere(['id' => 1, 'username' => 'abc']);// OR ( id=1 AND username='abc' )
$query->orWhere(['and', ['>', 'id', 10], ['username' => 'abc']]);// OR ( id>10 AND username='abc' )


// 判断记录是否存在  EXISTS / NOT EXISTS
$subQuery_3 = (new Query())->select("id")
    ->from("tableNameSub")
    ->where("tableNameSub.id=tableName.sub_id");
$query->andWhere(['exists', $subQuery_3]);// EXISTS 第二个参数必须是 Query查询实例


// LIKE / AND LIKE / OR LIKE
$query->andWhere(['like', 'tableName.name', 'abc']);// AND tableName.name LIKE '%abc%'
$query->andWhere(['like', 'tableName.name', 'abc%', false]);// AND tableName.name LIKE 'abc%'
$query->andWhere(['like', 'tableName.name', ['abc', 'def']]);// AND (tableName.name LIKE '%abc%' AND tableName.name LIKE '%def%')
$query->andWhere(['like', 'tableName.name', ['abc', 'def%'], false]);// AND (tableName.name LIKE 'abc' AND tableName.name LIKE 'def%')
$query->andWhere(['or like', 'tableName.name', ['abc', 'def']]);// AND (tableName.name LIKE '%abc%' OR tableName.name LIKE '%def%')


// 绑定查询参数（过滤用户输入，防止SQL 注入的攻击）
$query->andWhere('status=:status', [':status' => $status]);
$query->andWhere('status=:status')->addParams([':status' => $status]);

// BETWEEN / NOT BETWEEN
$query->andWhere(['between', 'id', 2, 5]);// AND id BETWEEN 2 AND 5


// andFilterWhere() :会过滤为空的查询
$query->andWhere(['username' => '', 'age' => '22']);// AND username='’AND age=22
$query->andFilterWhere(['username' => '', 'age' => '22']);// AND age=22   其中 username=''被过滤

// ORDER BY
$query->orderBy(['id' => SORT_ASC, 'username' => SORT_DESC]);
$query->orderBy('id ASC, username DESC');
$query->orderBy('id ASC')->addOrderBy('username DESC');// 追加排序方式
// GROUP BY
$query->groupBy(['id', 'status']);
$query->groupBy('id, status');
$query->groupBy(['id', 'status'])->addGroupBy('age');// 追加分组方式
// HAVING
$query->having(['status' => 1]);
$query->having(['status' => 1])->andHaving(['>', 'age', 30]);// 追加 HAVING 条件
// LIMIT
$query->limit(10)->offset(20);// LIMIT 20,10


// indexBy 查询结果集的索引
$query->indexBy('username');// 将把 username 字段的值作为数组的键名
$query->indexBy(function ($row) {
    return $row['id'] . '-' . $row['username'];
});// 将把 id-username 连在一起作为 键名


// 查询方法
$query->all();// 所有记录
$query->one();// 结果集第一条记录
$query->column();// 返回第一列的值
$query->scalar();// 结果集的第一行第一列的标量值
$query->exists();// 结果是否存在
$query->count();// 记录条数


// 其他拓展
// 获取大量数据时 使用 batch 或 each 方法代替 all 方法
//（前两者可以节省时间和内存，以前一直认为一次查询所有数据肯定比分批查询快，看来我错了，查询方式不一样处理的机制可能不一样）
$query = (new Query())->from('user');
foreach ($query->batch() as $user) {
    echo $user['username'];
    echo "<br/>";
}
foreach ($query->each() as $user) {
    echo $user['username'];
    echo "<br/>";
}


// batchInsert 批量插入数据
$result = Yii::$app->db->createCommand()
    ->batchInsert('tableName', ['username', 'age'], [
            ['张三', '21'],
            ['李四', '22'],
            ['王五', '25']
        ]
    )->execute();

// insert 插入单条记录
$result = Yii::$app->db->createCommand()
    ->insert("user", ['username' => '将军', 'age' => '36'])
    ->execute();