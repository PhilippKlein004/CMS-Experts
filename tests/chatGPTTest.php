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

class chatGPTTest extends TestCase
{

    public function setUp(): void
    {
        $this->chatGPT = new ChatGPT();
    }

    public function testGetResponse()
    {

        $result = $this->chatGPT->getResponse("Was ist 2+2? Gib nur die Zahl zurück!!!", false);
        $this->assertEquals("4", $result);


    }

    public function testTestAPI()
    {

        $this->expectException(Exception::class);
        $this->chatGPT->testAPI();

    }

}