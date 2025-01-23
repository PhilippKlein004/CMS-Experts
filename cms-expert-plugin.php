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

if ( !defined('ABSPATH') ) {
    exit;
}

// Lade die UI-Klasse
require_once plugin_dir_path(__FILE__) . 'UI/CMSExpertsPluginUI.php';
require_once plugin_dir_path(__FILE__) . 'service/chatGPT.php';
require_once plugin_dir_path(__FILE__) . 'service/DB.php';


function create_table():void { DB::create_table(); }


register_activation_hook( __FILE__, 'create_table' );

// Instanziiere die UI-Klasse
new CMSExpertsPluginUI();