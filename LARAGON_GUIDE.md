# 🐘 Laragon Installation & Setup Guide

Laragon is a blazing fast, lightweight, and powerful local development environment for PHP and MySQL. We recommend using Laragon to run the TexQura Enterprise System.

## Step 1: Download Laragon
1. Open your web browser and go to the official download page: [https://laragon.org/download/](https://laragon.org/download/)
2. Download the **Laragon Full** edition (this includes Apache, MySQL, and PHP 8+ out of the box).
3. Wait for the `.exe` file to finish downloading.

## Step 2: Install Laragon
1. Run the downloaded `laragon-wamp.exe` installer.
2. Choose your language and click **OK**.
3. Proceed with the installation setup. Keep the default installation path: `C:\laragon`.
4. On the preferences screen, check the boxes for:
   * **Run Laragon when Windows starts** (Optional but recommended).
   * **Auto virtual hosts** (Important for clean URLs).
   * **Auto start** (Starts Apache and MySQL automatically).
5. Click **Next**, then **Install**.
6. Once completed, check "Run Laragon" and click **Finish**.

## Step 3: Start the Server Environment
1. Open the Laragon application.
2. Click the big **Start All** button in the center of the window.
3. If Windows Defender prompts you for firewall access for Apache or MySQL, click **Allow access**.
4. You should now see Apache running on port `80` and MySQL running on port `3306` at the bottom of the Laragon window.

## Step 4: Deploying TexQura
1. Open your file explorer and navigate to `C:\laragon\www\`.
2. Move your entire `TexQura` project folder into this `www` directory. 
   *(Your path should look like: `C:\laragon\www\TexQura\index.php`)*
3. Open your web browser and type the following URL:
   ```text
   http://localhost/TexQura/setup/initialize.php