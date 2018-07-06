#!/usr/bin/env php

<?php

    define('MDT_CLI_API', true);
    
    require_once('../../../wp-load.php');
    
    
    require_once 'Order.php';
    require_once 'Product.php';
    require_once 'Category.php';
    
    fwrite(STDOUT, "ready\n\n");
    /* define('£', $instance);
    define('€', Msx_To_Wc); */
    while (true){
        $data = readline(' > ');
        if(!$data)continue;
        if($data == 'exit'){
            exit(0);
        }
        if($data == 'r'){
            exit(1);
        }
        $data = str_replace('§', '$mdt->', $data);
        if($data == 'help'){
            fwrite(STDOUT, "mmmh... no\n");
            exit(2);
        }
        try {
            $data = eval( 'return '. $data . ';' );
        } catch (Exception $e) {
            $data = $e;
        }
        $result = var_export( $data, true);
        fwrite(STDOUT, $result."\n");
    }
?>
