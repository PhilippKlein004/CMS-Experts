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

class PostHandler {

    public function __construct($chatGPT) {
        $this->chatGPT = $chatGPT;
    }

    private function checkForCategories():void {

        $categories = array('Produkt', 'Event', 'Beitrag');

        foreach ( $categories as $category_name ) {
            $categoryExists = term_exists($category_name, 'category');
            if ( !$categoryExists ) wp_insert_term($category_name, 'category');
        }

    }

    private function getCategoryIdByName($category_name) {
        $category = get_category_by_slug(sanitize_title($category_name));
        return $category ? $category->term_id : null;
    }

    /**
     * @throws Exception
     */

    public function checkForInappropriateContent( string $content ): void
    {

        $isAppropriate = $this->chatGPT->getResponse( "Wenn der folgende Inhalt anstößig, rassistisch, usw. ist gib 'false' zurück, falls in Ordnung 'true'. Hier ist der Inhalt: " . $content, false );
        if ( $isAppropriate === "false" ) throw new Exception("Anstößiger Inhalt erkannt!");

    }

    /**
     * @throws Exception
     */

    public function createPage($title, $content, $imageId, $postType): string {

        $javaScript = "<link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=accessibility_new,calendar_month' /><script> function toggleEasyLanguage(divId1, divId2) { const div1 = document.getElementById(divId1); const div2 = document.getElementById(divId2); if (div1 && div2) { div1.style.display = (div1.style.display === 'none') ? 'block' : 'none'; div2.style.display = (div2.style.display === 'none') ? 'block' : 'none'; } }</script>";
        $accessibilityButton = "<button class='access' onclick='toggleEasyLanguage(`easy`, `text`)' title='Einfache Sprache' style='position: fixed; width: 70px; height: 70px; bottom: 10px; right: 10px; cursor: pointer; background: rgb(0, 122, 255); border: 1px solid rgb(255, 255, 255); border-radius: 50%; display: flex; justify-content: center'><span class='material-symbols-outlined' style='font-size: 400%;'>accessibility_new</span></button>";

        $title = $title == '' ? $content : $title;
        $this->checkForCategories();

        $this->checkForInappropriateContent($content);
        $this->checkForInappropriateContent($title);

        switch ($postType) {
            case 'Produkt':
                $inputChatGPT = "Falls du die technischen Daten nicht kennst, dann lass die Tabelle aus! Das Selbe gilt auch für Seite des Herstellers. Du füllst eine ausführliche Produktseite über dieses Produkt: " . $content . ". <div id='text' style='display: block'>[Schreibe hier mind. 300 Wörter über das Produkt; Füge falls vorhanden technische Daten mittels <table> ein; Gib auch, falls dir bekannt die Seite des Herstellers mittels <a> an; Benutze auch Abschnitt im Text, welche du mittels den <h4>-Überschriften trennst]</div><div id='easy' style='display: none'> <hr> <h4>Beitrag in einfacher Sprache</h4> <hr> [Was du vorhin geschrieben hast in einfacher Sprache inkl. Abschnitte mit <h4>-Überschriften. Einfache Sätze ohne Komma!] </div>";
                break;
            case 'Event':
                $inputChatGPT = "Du füllst eine Event-Seite über folgendes Event aus: " . $content . ". Die Sachen, die in den [] stehen, füllst du bitte aus, der Rest ist Tabu! FÜGE KEIN HTML EIN!!! Falls du das Datum, den Ort, usw. nicht wissen solltest, dann gibt dort einfach [Eintragen] aus! Das hier gibst du mir ausgefüllt zurück: <div style='border-radius: 15px; border: 1px solid black; margin-bottom: 10px; padding: 7px; background: black; color: white; display: flex; align-items: center;'><div style='margin-right: 20px; margin-left: 10px'><span class='material-symbols-outlined' style='font-size: 300%'>calendar_month</span></div><div>Zeitraum: [Startdatum] - [Enddatum] <br>Veranstaltungsort: [Ort] <br>Uhrzeit: [Uhrzeit]</div></div> <h4>Informationen</h4> <div id='text' style='display: block'>[Ausführlicher Text zu dem Event mind. 300 Wörter]</div> <div id='easy' style='display: none'> <hr> <h4>Beitrag in einfacher Sprache</h4> <hr> [Was du vorhin geschrieben hast in einfacher Sprache inkl. Abschnitte mit <h4>-Überschriften. Einfache Sätze ohne Komma!] </div>";
                break;
            default:
                $postType = "Beitrag";
                $inputChatGPT = "Falls du etwas nicht weißt, lass es bitte aus! Du schreibst einen Beitrag über folgendes Thema: " . $content . ". Du bekommst Folgendes Formular, welches du mir ausgefüllt zurückgibst. Das was in den zwei [] steht, füllst du bitte aus, der Rest ist Tabu! <div id='text' style='display: block'> [Schreibe hier mind. 300 Wörter über dieses Thema, benutze <h4>-Überschriften um Abschnitte zu trennen] </div> <div id='easy' style='display: none'> <hr> <h4>Beitrag in einfacher Sprache</h4> <hr> [Was du vorhin geschrieben hast in einfacher Sprache inkl. Abschnitte mit <h4>-Überschriften. Einfache Sätze ohne Komma!] </div>";
                break;
        }

        $chatGPTResponse = $this->chatGPT->getResponse( $inputChatGPT, false );

        if ( str_contains($chatGPTResponse, "Es gab einen Fehler bei der Anfrage: cURL") ) throw new Exception("Timeout OpenAI!");
        else $chatGPTResponse = $javaScript . $chatGPTResponse . $accessibilityButton;

        // Erstelle die neue Seite
        $new_page = array(
            'post_title'   => $title,
            'post_content' => $chatGPTResponse,
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_author'  => get_current_user_id(),
        );

        $post_id = wp_insert_post($new_page);

        // Wenn ein Bild ausgewählt wurde, setze es als Featured Image
        if ( $imageId ) set_post_thumbnail($post_id, $imageId);

        $category_id = $this->getCategoryIdByName($postType);

        if ($category_id) wp_set_post_categories($post_id, [$category_id]);

        // URL der neuen Seite
        return get_permalink($post_id);
    }

    public function editText($text, $operation): string {

        $this->checkForInappropriateContent($text);

        switch( $operation ) {
            case 'summary':
                $inputChatGPT = "Bitte gib mir eine kompakte Zusammenfassung von einem gegebenen Text und gieße Paragraphen in HTML Paragraphen '<p style='animation-delay: 0s'>  </p>'. Gib ansonsten keinen weiteren HTML-Code zurück!!! Wichtig, jeder neue Paragraph hat zum vorherigen einen delay von 0.1s! WICHTIG: Falls du keine Zusammenfassung machen kannst, gib den Text 1:1 wieder zurück! Die Zusammenfassung soll 1/3 der Anzahl der Wörter des Originals entsprechen. Hier ist der Text: " . $text;
                break;
            case 'correct':
                $inputChatGPT = "Bitte gib mir den gegebenen Text korrekt zurück (Grammatik, Satzbau, ...) und gieße Paragraphen in HTML Paragraphen '<p style='animation-delay: 0s'>  </p>'. Gib ansonsten keinen weiteren HTML-Code zurück!!! Wichtig, jeder neue Paragraph hat zum vorherigen einen delay von 0.1s! Falls keine Korrektur möglich/nötig, dann gib den Text 1:1 wieder zurück! Hier ist der Text: " . $text;
                break;
            case 'points':
                $inputChatGPT = "Bitte gib mir die wichtigsten (max. 5, wiederhole dich nicht!) Stichpunkte des gegebnene Texts zurück und gieße die Stichpunkte als Paragraphen in HTML Paragraphen '<p style='animation-delay: 0s'>- [Text] </p>'. Gib ansonsten keinen weiteren HTML-Code zurück!!! Wichtig, jeder neue Paragraph hat zum vorherigen einen delay von 0.1s! WICHTIG: Falls keine Stichpunkte möglich, dann gib den Text 1:1 wieder zurück! Hier ist der Text: " . $text;
                break;
            case 'friendly':
                $inputChatGPT = "Bitte gib mir den gegebenen Text freundlicher zurück und gieße die Stichpunkte als Paragraphen in HTML Paragraphen '<p style='animation-delay: 0s'>- [Text] </p>'. Gib ansonsten keinen weiteren HTML-Code zurück!!! Wichtig, jeder neue Paragraph hat zum vorherigen einen delay von 0.1s! WICHTIG: Falls der Text schon freundlich genug, dann gib den Text 1:1 wieder zurück! Hier ist der Text: " . $text;
                break;
            case 'professional':
                $inputChatGPT = "Bitte gib mir den gegebenen Text als professioneller & freundlicher zurück, erweitere ihn ggf. (z.B. Anrede, Grüße, Danke usw.) und gieße die Stichpunkte als Paragraphen in HTML Paragraphen '<p style='animation-delay: 0s'>- [Text] </p>'. Gib ansonsten keinen weiteren HTML-Code zurück!!! Wichtig, jeder neue Paragraph hat zum vorherigen einen delay von 0.1s! WICHTIG: Falls der Text schon professionell genug, dann gib den Text 1:1 wieder zurück! Hier ist der Text: " . $text;
                break;
            default:
                return "Ein Fehler ist aufgetreten, bitte kontaktiere einen Administrator!";
        }

        $chatGPTResponse = $this->chatGPT->getResponse( $inputChatGPT, false );

        if ( str_contains($chatGPTResponse, "Es gab einen Fehler bei der Anfrage: cURL") ) return "Die Operation wurde nicht ausgeführt, bitte versuche es erneut!";

        return $chatGPTResponse;

    }

}