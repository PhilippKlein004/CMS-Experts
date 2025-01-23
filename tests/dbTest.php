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

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../service/DB.php';

class DBTest extends TestCase
{

    // ACHTUNG: alter Schlüssel wird überschrieben!

    public function testRoundTripDataBase()
    {

        // CRUD-Test der DB als Roundtrip-Test

        $key = "mySecretAPIKey";
        $keyInDB = "";

        // CREATE

        try {
            DB::create_table();
            DB::setAPIKey($key);
        } catch ( Exception ) {
            $this->markTestIncomplete("Fehler bei CREATE-Abschnitt geworfen!");
        }

        // READ

        $keyInDB = DB::getAPIKey();
        $this->assertEquals($key, $keyInDB);

        // UPDATE

        $key = "mySecretAPIKey2";
        DB::setAPIKey($key);

        $keyInDB = DB::getAPIKey();

        $this->assertEquals($key, $keyInDB);

        // DELETE

        DB::resetAPIKey();
        $keyInDB = DB::getAPIKey();
        $this->assertEquals("", $keyInDB);

        // RESET

        DB::setAPIKey("working API key");

    }

}