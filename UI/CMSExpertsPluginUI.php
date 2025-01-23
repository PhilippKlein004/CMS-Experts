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

session_start();

require_once plugin_dir_path(__FILE__) . '../service/PostHandler.php';
require_once plugin_dir_path(__FILE__) . '../service/chatGPT.php';
require_once plugin_dir_path(__FILE__) . '../service/DB.php';

if (!defined('ABSPATH')) exit;

class CMSExpertsPluginUI {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu(): void
    {
        wp_enqueue_style('mein-plugin-style', plugins_url('../style/style.css', __FILE__));
        wp_enqueue_script('mein-plugin-script', plugin_dir_url(__FILE__) . '../scripts/recorder.js', [], null, true);

        // Dynamischer Pfad für Assets
        wp_localize_script('mein-plugin-script', 'pluginData', [
            'assetsUrl' => plugin_dir_url(__FILE__) . '../assets/'
        ]);

        add_menu_page(
            'Neue Website erstellen',
            'Seite erstellen',
            'manage_options',
            'create-pages',
            [$this, 'admin_page_html'],
            plugin_dir_url(__FILE__) . '../assets/icon2.png',
            5
        );

        add_submenu_page(
            'create-pages',
            'Schreibwerkzeuge',
            'Schreibwerkzeuge',
            'manage_options',
            'writing-tools',
            [$this, 'writing_tools_html']
        );

        add_submenu_page(
            'create-pages',
            'Einstellungen',
            'Einstellungen',
            'manage_options',
            'settings',
            [$this, 'settings_html']
        );

    }

    public function admin_page_html(): void
    {

        if (!current_user_can('manage_options')) return;

        // Variablen zum Speichern der Benutzereingaben
        $titleInput = '';
        $themeInput = '';
        $postType = 'normal';
        $resultMessage = '';
        $site = '';
        $errorVisible = 'none';

        if (isset($_POST['submitButton'])) {

            // Eingaben aus dem Formular verarbeiten
            $titleInput = sanitize_text_field($_POST['titelField'] ?? '');
            $themeInput = sanitize_text_field($_POST['themeInput'] ?? '');
            $postType = sanitize_text_field($_POST['postType'] ?? '');
            $selectedImageId = intval($_POST['imageSelect'] ?? 0);

            // PostHandler-Instanz erstellen und Seite erstellen
            $postHandler = new PostHandler(new ChatGPT());

            try {
                $site = $postHandler->createPage($titleInput, $themeInput, $selectedImageId, $postType);
                $errorVisible = 'none';
                $resultMessage = "Seite wurde erfolgreich erstellt:&nbsp;<a href='" . esc_url($site) . "' target='_blank'>" . esc_html($site) . "</a>";
            } catch ( Exception ) {
                $errorVisible = 'block';
                $resultMessage = '';
            }

        }

        // UI des Admin-Menüs
        ?>
        <br>

        <div class="API_Notice" <?php echo DB::getAPIKey() != '' ? "style='display: none'" : "" ?>>
            <div class="notice_text">
                <img alt="CMS_Experts_Logo" class="ce_logo" src="<?php echo plugins_url('../assets/CMS_Experts_Logo.png', __FILE__); ?>">
                <h1>Willkommen im Plugin!</h1>
                <p>Bevor es losgehen kann, hinterlegen Sie einen API-Schlüssel für ChatGPT in den Einstellungen.</p>
            </div>
        </div>

        <script>
            document.onload = function reset() {
                const logo = document.getElementById("gpt_logo");
                logo.style.animation = "";
            }


            function rotate() {
                const logo = document.getElementById("gpt_logo");
                logo.style.animation = "rotate 1s linear infinite";
            }
        </script>

        <div class="wrap fancy">
            <h1>Neue Seite mit ChatGPT erstellen</h1>
            <p>Erstellen Sie mit ChatGPT neue Produkt-, Event- und Blogeinträge innerhalb von ein paar Sekunden.</p>
            <br>

            <form method="POST">
                <div class="interaction">
                    <div>
                        <img class="openAI" id="gpt_logo" alt="" src="<?php echo plugins_url('../assets/openai.svg', __FILE__); ?>">
                    </div>
                    <div>
                        <button  type="button" class="buttonMicrophone" id="microphoneButton"><img src="<?php echo plugins_url('../assets/microphone.png', __FILE__); ?>" alt='Mikrofon'></button>
                        <textarea
                                class="themeInput"
                                name="themeInput"
                                id="themeInput"
                                placeholder="Titel eingeben oder Thema diktieren..."
                                rows="2"
                                required><?php echo esc_attr($themeInput); ?></textarea>
                        <button class="CMS-button" name="submitButton" onclick="rotate()" style="cursor:pointer;" type="submit">Los</button>
                    </div>
                    <div class="titelDiv" style="display:none; margin-top: -35px;" id="titelDiv">
                        <input class="titelField" name="titelField" id="titelField" placeholder="Titel eingeben...(optional)" type="text" value="<?php echo esc_attr($titleInput); ?>">
                    </div>
                    <p class="error" style="display: <?php echo $errorVisible  ?>">Die Seite konnte nicht erstellt werden! Bitte versuchen Sie es erneut.</p>
                    <div class="radio-buttons">
                        <div>
                            <p>Beitragstyp wählen:</p>
                            <div class="button-list">
                                <label>
                                    <input type="radio" name="postType" value="Produkt" <?php checked($postType, 'Produkt'); ?>>
                                    Produktseite
                                </label>
                                <label>
                                    <input type="radio" name="postType" value="Event" <?php checked($postType, 'Event'); ?>>
                                    Event
                                </label>
                                <label>
                                    <input type="radio" name="postType" value="Beitrag" <?php checked($postType, 'Beitrag'); ?>>
                                    Normaler Post
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <select id="imageSelect" name="imageSelect">
                            <option value="">Wählen Sie ein Titelbild</option>
                            <?php
                            // Hole die Bilder aus der Mediathek
                            $images = get_posts(array(
                                'post_type'   => 'attachment',
                                'post_mime_type' => 'image',
                                'numberposts' => -1,
                                'post_status' => 'inherit',
                            ));

                            foreach ($images as $image) {
                                echo '<option value="' . esc_attr($image->ID) . '">' . esc_html($image->post_title) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <br>
                </div>
            </form>
        </div>
        <br>
        <div class="sitePreview">
            <?php if ($resultMessage) : ?>
                <div class="result"><?php echo $resultMessage; ?></div> <!-- Hier wird die Antwort angezeigt -->
                <br>
            <?php endif; ?>
            <iframe src="<?php echo esc_url($site); ?>"></iframe>
        </div>
        <?php
    }

    public function writing_tools_html(): void
    {

        if (!current_user_can('manage_options')) return;

        $newText = "
                        <p>Los geht's!</p>
                        <p style='animation-delay: 0.1s'>Schreibe einen Text und wähle eine Operation aus.</p>
                        ";
        $oldText = "";

        $postHandler = new PostHandler(new ChatGPT());

        try {

            if ( isset($_POST['summary']) ) {
                $oldText = stripslashes(sanitize_text_field($_POST['text'] ?? ''));
                $newText = $postHandler->editText($oldText, 'summary');
            }

            if ( isset($_POST['correct']) ) {
                $oldText = stripslashes(sanitize_text_field($_POST['text'] ?? ''));
                $newText = $postHandler->editText($oldText, 'correct');
            }

            if ( isset($_POST['points']) ) {
                $oldText = stripslashes(sanitize_text_field($_POST['text'] ?? ''));
                $newText = $postHandler->editText($oldText, 'points');
            }

            if ( isset($_POST['friendly']) ) {
                $oldText = stripslashes(sanitize_text_field($_POST['text'] ?? ''));
                $newText = $postHandler->editText($oldText, 'friendly');
            }

            if ( isset($_POST['professional']) ) {
                $oldText = stripslashes(sanitize_text_field($_POST['text'] ?? ''));
                $newText = $postHandler->editText($oldText, 'professional');
            }

        } catch ( Exception ) {
            $newText = "<p style='animation-delay: 0s'>Es gab einen Fehler, bitte versuchen Sie es erneut.</p>";
        }



        ?>
        <br>

        <script>

            document.onload = function reset() {

                const box = document.getElementById("outputBox");
                box.style.animation = "";

            }

            function load() {

                const box = document.getElementById("outputBox");
                box.style.animation = "loadingPulse 1s infinite";

            }

        </script>

        <div class="API_Notice" <?php echo DB::getAPIKey() != '' ? "style='display: none'" : "" ?>>
            <div class="notice_text">
                <img alt="CMS_Experts_Logo" class="ce_logo" src="<?php echo plugins_url('../assets/CMS_Experts_Logo.png', __FILE__); ?>">
                <h1>Willkommen im Plugin!</h1>
                <p>Bevor es losgehen kann, hinterlegen Sie einen API-Schlüssel für ChatGPT in den Einstellungen.</p>
            </div>
        </div>

        <div class="wrap fancy">
            <h1>&#128394; Writing Tools</h1>
            <p>Verbessern Sie Ihren Text mit der Hilfe von intelligenten Schreibwerkzeugen.</p>
            <br>
            <div class="writing_tools">
                <div class="wt_input">
                    <h2 style="color: white">Texteingabe</h2>
                    <form method="POST">
                        <textarea class="textInputWritingTools" name="text" maxlength="1000" placeholder="Text eingeben..." autocomplete="off" required><?php echo $oldText ?></textarea>
                        <p>Maximal 1000 Zeichen möglich!</p>
                        <div class="button_ray">
                            <button name="summary" onclick="load()">&#128196; Zusammenfassen</button>
                            <button name="correct" onclick="load()">&#128269; Korrekturlesen</button>
                            <button name="points" onclick="load()">&#128205; Stichpunkte</button>
                            <button name="friendly" onclick="load()">&#128578; Freundlich</button>
                            <button name="professional" onclick="load()">&#128188; Professionell</button>
                        </div>
                    </form>
                </div>
                <div class="wt_output">
                    <h2 style="color: white">Textausgabe</h2>
                    <div class="output" id="outputBox">
                        <?php echo $newText ?>
                    </div>
                </div>
            </div>
            <br>
        </div>
        <?php

    }

    public function settings_html(): void
    {

        if (!current_user_can('manage_options')) return;

        $chatGPT = new ChatGPT();
        $key = DB::getAPIKey();
        $keyStatus = "";

        if ( isset($_POST['api_key']) ) {
            $chatGPT->setApiKey(sanitize_text_field($_POST['apiKey'] ?? ''));
            try {
                $chatGPT->testAPI();
                $keyStatus = "Status: &#9989; API-Key ist gültig!";
                $key = sanitize_text_field($_POST['apiKey'] ?? '');
                DB::setAPIKey($key);
            } catch (Exception) {
                $keyStatus = "Status: &#10060; API-Key ist nicht gültig!";
            }
        }

        if ( isset($_POST['deleteKey']) ) {
            $key = "";
            DB::resetAPIKey();
        }

        ?>
        <br>

        <div class="wrap">
            <h1>Einstellungen</h1>
            <hr>
            <h3>1. API-Key für OpenAI ChatGPT</h3>
            <p>Hier können Sie Ihren API-Key für ChatGPT eingeben:</p>
            <form method="POST">
                <input type="text" name="apiKey" id="apiKey" placeholder="API-Key eingeben..." value="<?php echo get_option('apiKey'); ?>">
                <button name="api_key">Speichern</button>
            </form>
            <p><?php echo $keyStatus ?></p>
            <form method="POST">
                <p><?php echo $key != '' ? "Gespeicherter Schlüssel: " . $key : "" ?></p>
                <button name="deleteKey" <?php echo $key == '' ? "style='display: none'" . $key : "" ?>>Gespeicherten Schlüssel Löschen</button>
            </form>
            <br>
            <hr>
        </div>
        <?php

    }

}
?>