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

// cd User/Desktop/wp-content/plugins/cms-expert-plugin
// ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests

define('WP_ROOT', '/Users/philippklein/Documents/Coding/WordPress'); // Dein tatsächlicher Pfad

// Lade WordPress
require_once WP_ROOT . '/app/public/wp-load.php';