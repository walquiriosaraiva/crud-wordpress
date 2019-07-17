<?php

if ( !defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Plugin simples CRUD
Plugin URI: http://dev.wordpress.localhost
Description: Um simples plugin com (insert, update, delete e select).
Version: 1.0.0
Author: Walquirio Saraiva Rocha
Author URI: https://www.webbsb.com.br/
License: GPL2
*/

register_activation_hook(__FILE__, 'crudOperationsTable');
function crudOperationsTable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'userstable';
    $sql = "
            CREATE TABLE `$table_name` (
            `user_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(220) DEFAULT NULL,
            `email` varchar(220) DEFAULT NULL,
            PRIMARY KEY(user_id)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

add_action('admin_menu', 'addAdminPageContent');

function addAdminPageContent()
{
    add_menu_page('CRUD Simples', 'CRUD Simples', 'manage_options', __FILE__, 'crudAdminPage', 'dashicons-wordpress');
}

function wise_chat_shortcode($atts) {
    $html = "<h2> TESTE </h2>";
    return $html;
}
add_shortcode('wise-chat', 'wise_chat_shortcode');
add_action('wise-chat','wise_chat_shortcode');

function crudAdminPage()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'userstable';
    require_once('Conexao.php');
    $mensagem = '';

    if (isset($_POST['newsubmit'])) {
        $name = $_POST['newname'];
        $email = $_POST['newemail'];
        $conexao = Conexao::getInstance();
        try {
            $stmt = $conexao->prepare("INSERT INTO " . $table_name . " (name, email) VALUES (?, ?)");
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $email);
            if ($stmt->execute()) {
                $mensagem = 'Insert OK';
            }
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
        }
    }

    if (isset($_POST['uptsubmit'])) {

        $id = $_REQUEST['upt'];
        $name = $_POST['newname'];
        $email = $_POST['newemail'];

        $conexao = Conexao::getInstance();
        try {
            $stmt = $conexao->prepare("UPDATE " . $table_name . " SET name = ?, email = ? WHERE user_id = ?");
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $id);
            if ($stmt->execute()) {
                $mensagem = 'Update OK';
                $_GET['upt'] = null;
            }
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
        }
    }

    if (isset($_GET['del'])) {
        $del_id = $_GET['del'];
        $conexao = Conexao::getInstance();
        try {
            $stmt = $conexao->prepare("DELETE FROM " . $table_name . " WHERE user_id = ?");
            $stmt->bindParam(1, $del_id);
            if ($stmt->execute()) {
                $mensagem = 'Delete OK';
            }
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
        }
    }
    ?>
    <div class="wrap">

        <?php
        if (isset($_GET['upt'])) {
            $upt_id = $_GET['upt'];

            $conexao = Conexao::getInstance();
            try {
                $result = array();
                $stmt = $conexao->prepare("SELECT user_id, name, email FROM ".$table_name." WHERE user_id = {$upt_id};");
                if ($stmt->execute()) {
                    while ($rs = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $result[] = $rs;
                    }
                }
            } catch (Exception $e) {
                $mensagem = $e->getMessage();
            }

            foreach ($result as $print) {
                $nameEdit = $print['name'];
                $emailEdit = $print['email'];
                $user_id = $print['user_id'];
            }
        }
        ?>
        <h2>Plugin com insert, update, delete e select</h2>
        <h5><?php echo $mensagem; ?></h5>
        <table class="wp-list-table widefat striped">
            <thead>
            <tr>
                <th >ID</th>
                <th >Nome</th>
                <th >Email</th>
                <th >Ações</th>
            </tr>
            </thead>
            <tbody>
            <form action="" method="post">
                <tr>
                    <td><input type="text" name="uptid" id="uptid"
                               value="<?php echo isset($user_id) && $user_id ? $user_id : 'AUTOMATICO' ?>" disabled>
                    </td>
                    <td><input type="text" id="newname" name="newname"
                               value="<?php echo isset($nameEdit) && $nameEdit ? $nameEdit : null ?>"></td>
                    <td><input type="text" id="newemail" name="newemail"
                               value="<?php echo isset($emailEdit) && $emailEdit ? $emailEdit : null ?>"></td>
                    <td>
                        <?php
                        if (isset($_GET['upt'])) {
                            ?>
                            <button id='uptsubmit' name='uptsubmit' type='submit' value="uptsubmit">ATUALIZAR</button>
                            <a href='admin.php?page=crud%2Fcrud.php'></a>
                        <?php } else {
                            ?>
                            <button id="newsubmit" name="newsubmit" type="submit">INSERIR</button>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            </form>
            <?php
            $result = $wpdb->get_results("SELECT * FROM $table_name");
            foreach ($result as $print) {
                echo "
              <tr>
                <td >$print->user_id</td>
                <td >$print->name</td>
                <td >$print->email</td>
                <td ><a href='admin.php?page=crud%2Fcrud.php&upt=$print->user_id'><button type='button'>EDITAR</button></a> <a href='admin.php?page=crud%2Fcrud.php&del=$print->user_id'><button type='button'>EXCLUIR</button></a></td>
              </tr>
            ";
            }
            ?>
            </tbody>
        </table>
        <br>
        <br>

    </div>
    <?php
}