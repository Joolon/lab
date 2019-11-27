<?php


$password = '123456';
$options = [
    'cost' => 11, // 默认是10，用来指明算法递归的层数
        // mcrypt_create_iv — 从随机源创建初始向量
        // @param 初始向量大小
        // @param 初始向量数据来源
        // 可选值有： MCRYPT_RAND （系统随机数生成器）, MCRYPT_DEV_RANDOM （从 /dev/random 文件读取数据） 和 MCRYPT_DEV_URANDOM （从 /dev/urandom 文件读取数据）。 在 Windows 平台，PHP 5.3.0 之前的版本中，仅支持 MCRYPT_RAND。请注意，在 PHP 5.6.0 之前的版本中， 此参数的默认值为 MCRYPT_DEV_RANDOM。
        // 生成一个长度为22的随机向量
    'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
];
 
// @param 用户的密码
// @param 一个用来在散列密码时指示算法的密码算法常量
// PASSWORD_DEFAULT 使用 bcrypt 算法 (PHP 5.5.0 默认),该常量会随着 PHP 加入更新更高强度的算法而改变。 所以，使用此常量生成结果的长度将在未来有变化。 因此，数据库里储存结果的列可超过60个字符（最好是255个字符）
// PASSWORD_BCRYPT 使用 CRYPT_BLOWFISH 算法创建哈希。 这会产生兼容使用 "$2y$" 的 crypt()。 结果将会是 60 个字符的字符串， 或者在失败时返回 FALSE
// @param 一个包含有选项的关联数组。目前支持两个选项：salt，在散列密码时加的盐（干扰字符串），以及cost，用来指明算法递归的层数。省略后，将使用随机盐值与默认 cost。
$crypt = password_hash($password, PASSWORD_DEFAULT, $options);
// 或 $crypt = password_hash($password, PASSWORD_DEFAULT);
var_dump($crypt); // 长度60







