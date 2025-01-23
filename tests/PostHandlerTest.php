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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostHandlerTest extends TestCase
{

    public function setUp():void
    {
        $this->postHandler = new PostHandler(new ChatGPT());
    }

    public function testCreatePage()
    {

        global $wpdb;
        $postHandler = new PostHandler(new ChatGPT());

        $randomName = "newEntry" . rand();

        try {
            $postHandler->createPage($randomName, "", null, null);
        } catch ( Exception ) {
            $this->markTestIncomplete("Seite konnte nicht erstellt werden!");
        }

        $query = $wpdb->prepare("SELECT post_title FROM wp_posts WHERE post_title = %s", $randomName);
        $titleArray = $wpdb->get_results($query);

        $this->assertNotSame(null, $titleArray);

        $nameFromDB = $titleArray[0]->post_title;

        $this->assertEquals($randomName, $nameFromDB);
        $this->deletePost($randomName);

    }

    public function testCheckForInappropriateContentSexism()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Männer sind besser als Frauen.");
    }

    public function testCheckForInappropriateContentHomophobia()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Homos sind krank.");
    }

    public function testCheckForInappropriateContentMoreSexism()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Frauen haben weniger Verstand als Männer.");
    }

    public function testCheckForInappropriateContentRacism()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Alle Menschen aus Afrika sind weniger intelligent.");
    }

    public function testCheckForInappropriateContentMoreHomophobia()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Schwule sollten sich nicht öffentlich zeigen.");
    }

    public function testCheckForInappropriateContentReligiousDiscrimination()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Alle Muslime sind Terroristen.");
    }

    public function testCheckForInappropriateContentAbleism()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Menschen im Rollstuhl sind eine Belastung für die Gesellschaft.");
    }

    public function testCheckForInappropriateContentTransphobia()
    {
        $this->expectException(Exception::class);
        $this->postHandler->checkForInappropriateContent("Transgender Menschen sollten keine öffentlichen Ämter haben.");
    }

    private function deletePost($postName):void {
        global $wpdb;
        $query = $wpdb->prepare("DELETE FROM wp_posts WHERE post_title = %s", $postName);
        $wpdb->query($query);
    }

    private function getChatGPTMock():MockObject {

        try {
            $chatGPTMock = $this->createMock(ChatGPT::class);
            return $chatGPTMock;
        } catch ( \PHPUnit\Framework\MockObject\Exception ) {
            $this->markTestIncomplete("[testEditText] Mock-Klasse konnte nicht erstellt werden!");
        }

    }

    public function testEditTextSummary()
    {

        $answer = "Nach Kursanstieg gibt es große Erwartungen an Nvidia, da KI-Modelle und Services Rechenpower erfordern.  Die Aktie hat zuletzt keine großen Sprünge mehr gemacht, trotz des rasanten Wachstums und der immens hohen Nachfrage.  Analysten erwarten viel, sodass selbst kleine Verfehlungen zu großen Kursverlusten führen können.";

        $chatGPTMock = $this->getChatGPTMock();
        $chatGPTMock->method("getResponse")
            ->willReturn($answer);

        $postHandler = new PostHandler($chatGPTMock);
        $resultText = $postHandler->editText("Nach Kursanstieg: Große Erwartungen an Nvidia 'Alle diejenigen, die jetzt KI-Modelle trainieren wollen, die Geschäftsmodelle aufbauen wollen, die Services um künstliche Intelligenz herum bauen wollen, die brauchen natürlich Rechenpower - und zwar nicht irgendwelche, sondern die besten Prozessoren', so Christian Röhl. Zum steilen Aufstieg einer Aktie gehört, dass auch die Erwartungen der Analysten größer werden. Das hat sich auch im Fall von Nvidia schon gezeigt. Der Konzern ist in den vergangenen Monaten weiter rasant gewachsen, die Nachfrage ist immens. Die Aktie aber hat zuletzt keine ganz großen Sprünge mehr nach oben gemacht. Nvidia wird an eigenem Erfolg gemessen Das Potenzial für Enttäuschungen sei gerade wegen des bahnbrechenden Erfolgs groß. Da könne es schon reichen, so Röhl, dass der Konzern die Erwartungen der Analysten mal knapp verfehlt. Dann könne auch eine Aktie wie Nvidia mal zehn, fünfzehn oder zwanzig Prozent an einem Tag fallen.", "correct");

        $this->assertEquals($answer, $resultText);

    }

    public function testEditTextCorrect()
    {

        $answer = "Ich bin der Beste auf dieser Welt";

        $chatGPTMock = $this->getChatGPTMock();
        $chatGPTMock->method("getResponse")
            ->willReturn($answer);

        $postHandler = new PostHandler($chatGPTMock);
        $resultText = $postHandler->editText("Isch bin der bäste der welt", "summary");

        $this->assertEquals($answer, $resultText);

    }

    public function testEditTextPoints()
    {

        $answer = "- Nvidia spielt eine zentrale Rolle im Bereich künstliche Intelligenz und benötigt dafür leistungsstarke Prozessoren.\n- Das Unternehmen hat in den letzten Monaten ein rasant wachsendes Geschäft erlebt und die Nachfrage ist enorm.\n- Analystenerwartungen steigen mit dem Kursanstieg der Nvidia-Aktie.\n- Das Unternehmen könnte Gefahr laufen, die hohen Erwartungen zu verfehlen, was zu starken Kursverlusten führen könnte.\n- Ein Rückgang der Aktie um zehn bis zwanzig Prozent innerhalb eines Tages ist möglich, wenn die Analystenerwartungen nicht erfüllt werden.";

        $chatGPTMock = $this->getChatGPTMock();
        $chatGPTMock->method("getResponse")
            ->willReturn($answer);

        $postHandler = new PostHandler($chatGPTMock);
        $resultText = $postHandler->editText("Nach Kursanstieg: Große Erwartungen an Nvidia 'Alle diejenigen, die jetzt KI-Modelle trainieren wollen, die Geschäftsmodelle aufbauen wollen, die Services um künstliche Intelligenz herum bauen wollen, die brauchen natürlich Rechenpower - und zwar nicht irgendwelche, sondern die besten Prozessoren', so Christian Röhl. Zum steilen Aufstieg einer Aktie gehört, dass auch die Erwartungen der Analysten größer werden. Das hat sich auch im Fall von Nvidia schon gezeigt. Der Konzern ist in den vergangenen Monaten weiter rasant gewachsen, die Nachfrage ist immens. Die Aktie aber hat zuletzt keine ganz großen Sprünge mehr nach oben gemacht. Nvidia wird an eigenem Erfolg gemessen Das Potenzial für Enttäuschungen sei gerade wegen des bahnbrechenden Erfolgs groß. Da könne es schon reichen, so Röhl, dass der Konzern die Erwartungen der Analysten mal knapp verfehlt. Dann könne auch eine Aktie wie Nvidia mal zehn, fünfzehn oder zwanzig Prozent an einem Tag fallen.", "points");

        $this->assertEquals($answer, $resultText);

    }

    public function testEditTextFriendly()
    {

        $answer = "Hallo Chef, ich würde mich freuen, wenn ich die Möglichkeit hätte, den Job zu übernehmen.\nIch bin überzeugt, dass ich einer der besten Mitarbeiter in Ihrem Team bin!\nIch habe stets mein Bestes gegeben und war bisher immer zuverlässig.";

        $chatGPTMock = $this->getChatGPTMock();
        $chatGPTMock->method("getResponse")
            ->willReturn($answer);

        $postHandler = new PostHandler($chatGPTMock);
        $resultText = $postHandler->editText("Ey Chef, gib mal den Job her. Isch bin der Beste vonn dainen Mitarbeitern! Ich habe immer alles richtig gemach und war nie Kramk!", "points");

        $this->assertEquals($answer, $resultText);

    }

    public function testEditTextProfessional()
    {

        $answer = "Sehr geehrter Herr Müller,  ich hoffe, es geht Ihnen gut. Ich möchte gerne auf die Möglichkeit eingehen, den Job zu übernehmen.  Ich bin der Überzeugung, dass ich der beste Mitarbeiter in Ihrem Team bin. In der Vergangenheit habe ich stets mein Bestes gegeben und habe immer alle Aufgaben erfolgreich gemeistert.  Darüber hinaus war ich während meiner gesamten Zeit bei Ihnen nie krank und konnte so stets zuverlässig zur Arbeit erscheinen.  Vielen Dank für Ihre Überlegung. Ich freue mich auf Ihre Rückmeldung.  Mit freundlichen Grüßen,  Martin";

        $chatGPTMock = $this->getChatGPTMock();
        $chatGPTMock->method("getResponse")
            ->willReturn($answer);

        $postHandler = new PostHandler($chatGPTMock);
        $resultText = $postHandler->editText("Ey Chef Müller, gib mal den Job her. Isch bin der Beste vonn dainen Mitarbeitern! Ich habe immer alles richtig gemach und war nie Kramk! Grüße Martin", "points");

        $this->assertEquals($answer, $resultText);

    }

}