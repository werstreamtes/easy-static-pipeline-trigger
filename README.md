### Easy Static Pipeline Trigger
Contributors: Andreas Martin  
Tags: static, gitlab, deploy, trigger, pipeline  
Requires at least: 5.0  
Tested up to: 6.5  
Requires PHP: 7.4  
Stable tag: 1.0.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Trigger the GitLab pipeline to generate a static version of your WordPress site with a single click – directly from the admin area.


**Easy Static Pipeline Trigger** allows site editors and administrators to trigger a GitLab CI/CD pipeline for static site generation directly from the WordPress backend.

This is useful if you're using a static site generator in your GitLab project to deploy a static version of your WordPress site.

### Features:

- Trigger a pipeline manually from the WordPress menu or admin toolbar
- Display success or error messages based on the GitLab API response
- Configuration form to store your GitLab Project ID and trigger token

### Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings → Easy Static** to enter your GitLab project ID and trigger token
4. Use the “Jetzt veröffentlichen” submenu or the admin bar button to trigger the pipeline

### What does this plugin do?

It triggers a GitLab pipeline using your personal trigger token and project ID to initiate a static export job.

### License

This plugin is licensed under the GPLv2 or later.
