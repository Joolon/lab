<?php

// pcntl 拓展只能在类 UNIX 系统上运行，像 WINDOWS 等非类 UNIX 系统无法运行
// UNIX 系统上 pcntl 拓展一般默认会自动开启

// 守护进程   一个运行在后台的进程，不占用控制终端，也不受终端控制

$pid = pcntl_fork();
if( $pid < 0 ){
    exit('fork error.');
} else if( $pid > 0 ) {
    // 主进程退出
    exit();
}
// 子进程继续执行

// 最关键的一步来了，执行setsid函数！
if( !posix_setsid() ){
    exit('setsid error.');
}

// 理论上一次fork就可以了
// 但是，二次fork，这里的历史渊源是这样的：在基于system V的系统中，通过再次fork，父进程退出，子进程继续，保证形成的daemon进程绝对不会成为会话首进程，不会拥有控制终端。

$pid = pcntl_fork();
if( $pid  < 0 ){
    exit('fork error');
} else if( $pid > 0 ) {
    // 主进程退出
    exit;
}

// 子进程继续执行

// 设置进程标题
cli_set_process_title('testtesttest');

// 该进程 将会一直执行
while(1){
    sleep(1);
    file_put_contents('./log.txt',"test-".date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
    echo "test-".date('Y-m-d H:i:s').PHP_EOL;
}



