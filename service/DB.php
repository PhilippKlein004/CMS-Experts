<?php

/**
 * @package CMSExpertPlugin
 * Plugin Name: CMS Experts Plugin
 * Plugin URI: https://www.example.org
 * Description: Erstellen Sie mit ChatGPT einzigartige Produkt-, Event- und Blogbeiträge oder bearbeiten Sie Ihre Texte mit der Hilfe von intelligenten Schreibwerkzeugen. ChatGPT kann Fehler machen!
 * Version: 2.0.1
 * Author: CMS Experts GmbH & Co. KG
 * Author URI: https://www.example.org
 * License: MIT License
 */

class DB
{

    public static function create_table():void {

        global $wpdb;

        //$wpdb->show_errors();

        $wpdb->query("CREATE TABLE IF NOT EXISTS APIKey ( id INT PRIMARY KEY, key_value VARCHAR(255) );");

        $query = $wpdb->prepare("SELECT key_value FROM APIKey WHERE id = %d", 1);
        $keyArray = $wpdb->get_results($query);

        //var_dump($keyArray);

        if ( count($keyArray) === 0 ) {
            $wpdb->query("INSERT INTO APIKey (id, key_value) VALUES (1, '');");
        }

    }

    public static function setAPIKey($key):void {

        global $wpdb;

        $query = $wpdb->prepare("UPDATE APIKey SET key_value = %s WHERE id = 1;", $key);
        $wpdb->query($query);

    }

    public static function getAPIKey():string {

        global $wpdb;

        $query = $wpdb->prepare("SELECT key_value FROM APIKey WHERE id = %d", 1);
        $keyArray = $wpdb->get_results($query);

        if ( $keyArray == null ) return "";

        return $keyArray[0]->key_value;

    }

    public static function resetAPIKey():void {

        global $wpdb;

        $wpdb->query("UPDATE APIKey SET key_value = '' WHERE id = 1;");

    }

}