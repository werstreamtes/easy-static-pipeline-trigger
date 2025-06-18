<?php
/**
 * Plugin Name: easy_static Pipeline Trigger
 * Description: Triggers a static site generation pipeline.
 * Version: 1.0.0
 * Author: Andreas Martin
 * License: GPL2+
 * Text Domain: easy_static-pipeline-trigger
 */

if (!defined("ABSPATH")) {
    exit;
}

define("easy_static_TRIGGER_PATH", plugin_dir_path(__FILE__));
define("easy_static_TRIGGER_URL", plugin_dir_url(__FILE__));

add_action("admin_menu", "easy_static_register_admin_menu");
add_action("admin_bar_menu", "easy_static_add_admin_bar_button", 100);
add_action("admin_init", "easy_static_handle_trigger_from_bar");

function easy_static_register_admin_menu()
{
    add_menu_page(
        "easy_static Config",
        "Easy Static",
        "manage_options",
        "easy_static-config",
        "easy_static_admin_page_content",
        "dashicons-update",
        60
    );

    add_submenu_page(
        "easy_static-config",
        "Jetzt veröffentlichen",
        "Jetzt veröffentlichen",
        "edit_pages",
        "easy_static-trigger",
        "easy_static_trigger_page_content"
    );

}

function easy_static_add_admin_bar_button($wp_admin_bar)
{
    $token = esc_attr(get_option("easy_static_token", ""));
    $projectID = esc_attr(get_option("easy_static_project_id", ""));

    if (!current_user_can("manage_options") || !$token || !$projectID) {
        return;
    }

    $args = [
        "id" => "easy_static-trigger",
        "title" => "🚀 Jetzt veröffentlichen",
        "href" => admin_url("admin.php?page=easy_static-trigger"),
        "meta" => [
            "title" => "Statische Seiten neu generieren",
            "class" => "easy_static-trigger-button",
        ],
    ];
    $wp_admin_bar->add_node($args);
}

function easy_static_handle_trigger_from_bar()
{
    if (isset($_GET["page"], $_GET["trigger"]) && $_GET["page"] === "easy_static-trigger") {
        wp_safe_redirect(admin_url("admin.php?page=easy_static-trigger"));
        exit;
    }
}

function easy_static_trigger_page_content()
{

    $ch = curl_init();
    $token = esc_attr(get_option("easy_static_token", ""));
    $projectID = esc_attr(get_option("easy_static_project_id", ""));

    curl_setopt($ch, CURLOPT_URL, sprintf("https://gitlab.com/api/v4/projects/%s/trigger/pipeline", $projectID));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $post = array(
        "token" => $token,
        "ref" => "main",
        "variables[JOB_SCHEDULED]" => "static-publish",
        "variables[BROWSER_PERFORMANCE_DISABLED]" => "true",
        "variables[CI_DEPLOY_FREEZE]" => "true"
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    $result = curl_exec($ch);
    $response = json_decode($result, true);

    ?>
    <div class="wrap">
        <h1>Easy Static</h1>
        <?php if (isset($response["status"]) && $response["status"] == "created") {
            ?>
            <div class="notice notice-success">
                <p>Der statische Export (#<?= $response["id"]; ?>) wurde erfolgreich gestartet. Dieser Vorgang kann
                    einige Minuten in Anspruch nehmen.</p>
                <p>Den aktuellen Fortschritt kannst du im Detail <a href="<?= $response["web_url"]; ?>"
                                                                    target="_blank">hier</a> verfolgen.</p>
            </div>
            <?php
        } else {
            ?>
            <div class="notice notice-error">
                <p>Der Statische Export konnte nicht gestartet werden.</p>
                <p><code><?= $response["message"]; ?></code></p>
            </div>
            <?php
        }

        if (curl_errno($ch)) {
            echo "Error:" . curl_error($ch);
        }
        curl_close($ch);

        ?>
    </div>
    <?php
}

function easy_static_admin_page_content()
{
    $token = esc_attr(get_option("easy_static_token", ""));
    $jobName = esc_attr(get_option("easy_static_job_name", ""));
    $projectID = esc_attr(get_option("easy_static_project_id", ""));

    // Formular wurde gesendet
    if (isset($_POST["easy_static_form_submitted"])) {
        check_admin_referer("easy_static_save_form");

        $token = sanitize_text_field($_POST["easy_static_token"]);
        update_option("easy_static_token", $token);

        $jobName = sanitize_text_field($_POST["easy_static_job_name"]);
        update_option("easy_static_job_name", $jobName);

        $projectID = sanitize_text_field($_POST["easy_static_project_id"]);
        update_option("easy_static_project_id", $projectID);

        echo "<div class=\"notice notice-success\"><p>Einstellungen gespeichert.</p></div>";
    }

    ?>
    <div class="wrap">
        <h1>Easy Static</h1>
        <form method="post">
            <?php wp_nonce_field("easy_static_save_form"); ?>
            <table class="form-table">
                <!--tr>
                        <th scope="row"><label for="easy_static_job_name">Jobname</label></th>
                        <td><input type="text" name="easy_static_job_name" id="easy_static_job_name"
                                   value="<?php echo $jobName; ?>"
                                   class="regular-text"/></td>
                    </tr-->
                <tr>
                    <th scope="row"><label for="easy_static_project_id">Projekt ID</label></th>
                    <td><input type="text" name="easy_static_project_id" id="easy_static_project_id"
                               value="<?php echo $projectID; ?>"
                               class="regular-text"/></td>
                </tr>
                <tr>
                    <th scope="row"><label for="easy_static_token">GitLab Token</label></th>
                    <td><textarea name="easy_static_token" id="easy_static_token"
                                  class="large-text"><?php echo $token; ?></textarea></td>
                </tr>
            </table>

            <input type="hidden" name="easy_static_form_submitted" value="1"/>
            <?php submit_button("Speichern"); ?>
        </form>
    </div>
    <?php
}
