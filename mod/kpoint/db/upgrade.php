<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function xmldb_kpoint_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019061200) {
        // Change status to be allowed to be null.
        $table = new xmldb_table('kpoint');
        $description = new xmldb_field('description');
        $intro = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'big');
        $introformat = new xmldb_field('introformat', XMLDB_TYPE_CHAR, '4');

        if (!$dbman->field_exists($table, $intro)) {
            $dbman->add_field($table, $intro);
        }

        if (!$dbman->field_exists($table, $introformat)) {
            $dbman->add_field($table, $introformat);
        }

        // Conditionally change field type.
        if ($dbman->field_exists($table, $description)) {
            $sql = "update mdl_kpoint k set k.intro = k.description,k.introformat=1 where (k.intro is null or k.intro='') and (k.description is not null and k.description != '')";
            $DB->execute($sql,null);
            $sql = "alter table mdl_kpoint drop description";
            $DB->execute($sql,null);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2019061200, 'kpoint');
    }   
}
