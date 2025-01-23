/*
 * @package CMSExpertPlugin
 * Plugin Name: CMS Experts Plugin
 * Plugin URI: https://www.example.org
 * Description: Erstellen Sie mit ChatGPT einzigartige Produkt-, Event- und Blogbeiträge oder bearbeiten Sie Ihre Texte mit der Hilfe von intelligenten Schreibwerkzeugen. ChatGPT kann Fehler machen!
 * Version: 2.0.1
 * Author: CMS Experts GmbH & Co. KG
 * Author URI: https://www.example.org
 * License: MIT License
 */

document.addEventListener("DOMContentLoaded", function () {
    let recognition;
    let IsRecognizing = false;

    console.log("Microphone Script geladen");
    console.log("Assets URL:", pluginData.assetsUrl);


    //Funktioniert nur mit Chrome und https:// vor der URL !!
    if ("webkitSpeechRecognition" in window) {
        recognition = new webkitSpeechRecognition();
        recognition.continuous = true; // Ständig zuhören
        recognition.lang = "de-DE"; // Sprache auf Deutsch setzen
        recognition.interimResults = true; // Zeigt Zwischenergebnisse an

        recognition.onresult = function (event) {
            const transcript = event.results[event.resultIndex][0].transcript;
            console.log(transcript);
            document.getElementById("themeInput").value = transcript; // Setze das erkannten Thema ins Input-Feld
        };

        // Start/Stop Button
        document.getElementById("microphoneButton").addEventListener("click", () => {
            if (IsRecognizing) {
                recognition.stop();
                IsRecognizing = false;
                document.getElementById("microphoneButton").innerHTML = `<img src="${pluginData.assetsUrl}microphone.png" alt='Mikrofon'>`;
            } else {
                recognition.start();
                IsRecognizing = true;
                document.getElementById("microphoneButton").innerHTML = `<img src="${pluginData.assetsUrl}x-mark-16.png" alt='Stop'>`;
                document.getElementById("titelDiv").style.display = "flex";
            }
        });
    } else {
        console.log("Keine WebKitSpeechRecognition erkannt, bitte überprüfe den Browser und die Einstellungen");
    }
});
