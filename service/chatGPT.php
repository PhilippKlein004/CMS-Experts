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

class ChatGPT {

    private string $apiKey = '';

    public function __construct() {}

    public function setApiKey($apiKey):void {
        $this->apiKey = $apiKey;
    }

    /**
     * @throws Exception
     */

    public function testAPI():void {
        if ( $this->getResponse('Hallo ChatGPT, wenn du diese Nachricht bekommen hast, gib "true" zurück!', true) !== 'true' ) throw new Exception("API-Schlüssel ist ungültig!");
    }

    // Methode, die eine Anfrage an die ChatGPT-API sendet und die Antwort zurückgibt
    public function getResponse($input, $isPingTest):string {

        if ( !$isPingTest ) $this->apiKey = DB::getAPIKey();

        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $input]],
        ];

        $args = [
            'body'        => json_encode($data),
            'headers'     => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => 30,
        ];

        // API-Anfrage ausführen
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return 'Es gab einen Fehler bei der Anfrage: ' . $response->get_error_message();
        }

        // Antwort verarbeiten
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        // Überprüfen, ob eine Antwort vorhanden ist
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }

        return 'Keine Antwort erhalten.';
    }

}