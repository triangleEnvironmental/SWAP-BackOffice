<!-- PROJECT SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/triangleEnvironmental/SWAP-BackOffice">
    <img src="https://raw.githubusercontent.com/triangleEnvironmental/SWAP-BackOffice/main/resources/doc/graphic.png" alt="Logo" width="80" height="80">
  </a>

  <h3 align="center">SWAP Back-Office</h3>

  <p align="center">
    Keep your city clean with a swipe of your finger! You can find out information about garbage collection schedule, collection services, and best practices of waste disposal in the selected area. You can now let people know if there is an issue with garbage in your area anonymously or with a User ID Then you can watch the progression as the problem is addressed more effectively.
  </p>
</div>

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#01-introduction">Introduction</a></li>
    <li><a href="#02-technologies">Technologies</a></li>
    <li><a href="#03-architecture">Architecture</a></li>
    <li><a href="#04-functionalities">Functionalities</a></li>
    <li><a href="#05-system-actors">System Actors</a></li>
    <li><a href="#06-relational-diagram">Relational Diagram</a></li>
    <li>
      <a href="#07-getting-started">Getting Started</a>
      <ul>
        <li><a href="#71-prerequisites">Prerequisites</a></li>
        <li><a href="#72-installation">Installation</a></li>
      </ul>
    </li>
    <li>
      <a href="#08-notice">Notice</a>
      <ul>
        <li><a href="#81-project-structure">Project Structure</a></li>
        <li><a href="#82-authentication">Authentication</a></li>
        <li><a href="#83-roles-and-permissions">Roles and Permissions</a></li>
        <li><a href="#84-crawler">Crawler</a></li>
        <li><a href="#85-seeder">Seeder</a></li>
        <li><a href="#86-postgis">PostGIS</a></li>
        <li><a href="#87-using-existing-script">Using Existing Script</a></li>
        <li><a href="#88-notification">Notification</a></li>
        <li><a href="#89-admin-template">Admin Template</a></li>
        <li><a href="#810-cluster-marker">Cluster Marker</a></li>
        <li><a href="#811-file-storage">File Storage</a></li>
        <li><a href="#812-activity-log">Activity Log</a></li>
        <li><a href="#813-modify-login-function-fortify">Modify Login Function Fortify</a></li>
        <li><a href="#814-backup">Backup</a></li>
      </ul>
    </li>
  </ol>
</details>

## 01. Introduction

This project is a revamped version to improve three main objectives over the first version:

* **Flexibility**: access control configurable in database level, Geodata operate directly by query, Configurable settings,
* **Security**: Hashed password, Verify JWT, Middleware, Permission checked, Activity Log
* **Complexity**: Server-side marker clustering, Pagination, infinite scroll, lazy load.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 02. Technologies

* **Laravel 9** : for back-office and API
* **Vue 3** : for back-office front end
* **Postgres + PostGIS** : as database
* **Firebase** : for Waste Tracker app authentication and push notification
* **Google Map** : for map view on back-office, Waste Tracker app and Service Tracker app.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 03. Architecture

![Architecture.svg](https://raw.githubusercontent.com/triangleEnvironmental/SWAP-BackOffice/main/resources/doc/Architecture.svg)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 04. Functionalities

* Authentification
  * Input phone number page
  * Input OTP page
  * Resend OTP after 120 seconds
  * Get start with first name and last name
  * Select location page
  * Logout
* Profile Management
  * Edit profile page
  * Update first name and last name
  * Update location and address
  * Upload profile photo
  * Delete profile photo
  * Display service provider and municipality name
* Settings
  * Settings page layout
  * Change language
* Reporting
  * Create a report
  * View report listing
  * View reports on map
  * View report details
  * View report comments
  * View moderation history
* Home Page
  * View municipality of current location
  * Random clean city tip cards
  * Create report button
  * Page navigation on home page
* FAQ
  * List clean city tip under available sector
  * View clean city tip detail
* Notification
  * Receive push notification
  * Tap on notification to navigate to notification detail page
  * Notification detail page
  * List notifications page
  * Mark read all notifications
  * Count notification not yet read on bell icon
* My Report History
* Terms of Service Page
* Privacy Policy Page
* About Page
* Citizen comment on reports

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 05. System Actors

* **Super Administrator**: System admin who can manage overall configuration, sectors, FAQs, Institution, Users, etc.
* **Institutional Administrator**: Manage data in scope of their own institution includes adding service areas, manage users inside the same institution (Service provider, municipality)
* **Institutional Member**: Inside a service provider or municipality, members can moderate reports and send notification to citizens whose location is within the service area.
* **Citizen**: Users who use Waste Tracker app.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 06. Relational Diagram

![Architecture.svg](https://raw.githubusercontent.com/triangleEnvironmental/SWAP-BackOffice/main/resources/doc/ER.svg)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 07. Getting Started

### 7.1. Prerequisites

* Vue 3
* Laravel 9.19 or above
* Firebase CLI
* Postgres 11 or above
* Node.js 16 or above

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### 7.2. Installation

* Repo: https://github.com/triangleEnvironmental/SWAP-BackOffice
* Set up firebase adminsdk
  * Download `firebase-adminsdk.json` from Firebase Project settings - (dev) > Service accounts > Generate new private key
  * Copy it to `resources/credential/firebase-adminsdk.json`
* Setup database
  * For example you use postgres as database user
  * `sudo -u postgres createdb swap-db`
  * `sudo -u postgres psql`
  * `\\c swap-db`
  * `CREATE EXTENSION postgis;`
  * `\\q`
* copy `.env.example` to `.env` then update the `.env` file
* `composer install`
* `npm install`
* `php artisan key:generate`
* `php artisan optimize`
* `php artisan migrate --seed`
* `php artisan storage:link`
* `php artisan update:permission`
* Run project
  * `php artisan serve --host=0.0.0.0:8000`
  * `php artisan queue:listen --timeout=0`
  * `npm run dev`

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## 08. Notice

#### 8.1. Project Structure
Back office was started from Laravel Jetstream with Intertia. For more information about project structure, please check https://jetstream.laravel.com/2.x/stacks/inertia.html.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.2. Authentication

* **Laravel Sanctum**: since we are using Laravel Jetstream, it comes with Laravel Sanctum for authentication.
* **Firebase Auth**: is required to send OTP through SMS. Once a citizen user authenticates with Firebase Auth, a token ID is passed to backend through API to verify and generate an access token.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.3. Roles and Permissions
* This project has 4 predefined roles:
  1. Super Admin
  2. Institutional Admin
  3. Institutional Member
  4. Citizen
* Permissions
  * All permissions will be attached to user's session in `AuthServiceProvider.php`:
```php
...
use App\Classes\Permissions;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    ...
    public function boot()
    {
        ...
        Permissions::registerAllPermissions();
    }
}
```
  * To create a new permission, simply create a php file in `app/Permissions/` (follow the existing file in the same directory) and add the new permission:

```php
<?php

namespace App\Permissions;

use App\Classes\Permissions;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['do-something'])->to([1, 2, 3]), // number indicate roles
    permit(['do-something-else-on-model'])->toWho(function(User $user, Model $model) {
        $should_authorize = true; // Your own logic
        return $should_authorize;
    }),
]);
```
  * Run `php artisan update:permission` to update 

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.4. Crawler

To keep Privacy Policy and Terms of Use up to date, we made a crawler using Taiko (https://taiko.dev/) to check the updated content once a day.
* To update the contents manually, run `php artisan termly:update`
* Since Taiko uses node JS, you need to install the following node dependencies,`
```shell
npm install taiko express body-parser
```
* If you are using Ubuntu, you might need to install `libasound2` as well
```shell
sudo apt-get install libasound2
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.5. Seeder

After setting up the back-office, we need to kickstart with pre-config data.
```shell
php artisan db:seed
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.6. PostGIS

To make it easier to work with queries related to GIS, we use **laravel-postgis**. For further information, please check https://github.com/mstaack/laravel-postgis.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.7. Using Existing Script
* `setup-schedule.sh` : this script is to add a cronjob for this project on Ubuntu server
* `setup-supervisor.sh` : this script is to setup supervisor automatically on Ubuntu server
* `php artisan termly:update` : to fetch/update content of privacy policy and terms of use
* `php artisan update:permission` : to reassign permissions to roles
* `./build_test.sh` : to automatically update the latest code on staging server

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.8. Notification

* Currently, push notification will be sent by the following events:
  * Report status has been changed
  * Someone comments on a report (not anonymous)
  * Someone assigns another to moderate a report
  * Broadcast a message to citizens under the scope of an institution
  * Broadcast a message to citizens under an area
  * How to define route page in app
* A `Notification` model belongs to a `MasterNotification`. A `MasterNotification` contains notification content while a `Notification` contains information of a notification inbox for an individual user. 
* Once a `Notification` model is created, a push notification task is added to queue.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.9. Admin Template

* We use **AdminOne** template since it simple, easy to configure and works very well with `Laravel Jetstream`. https://github.com/justboil/admin-one-vue-tailwind

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.10. Cluster Marker

* To make displaying markers works well on large scale of data, we cluster the markers on server-side use `ST_ClusterDBScan` function. For more detail, check https://postgis.net/docs/ST_ClusterDBSCAN.html.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.11. File Storage

* To enable storage: `php artisan storage:link`
* The files we be store in `public` folder in local storage.
* The following type of images correspond to `directory` name:
  - Report images: `report_images`
  - comment images: `comment_images`
  - User profile photos: `profile_photos`
  - SP logo: `institution_logos`
  - CKEditor images: `ckeditor-images`
  - Sector icons: `sector_icons`

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.12. Activity Log

* For logging activity, we use `spatie/laravel-activitylog`. For more information, check https://github.com/spatie/laravel-activitylog.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.13. Modify Login Function Fortify

With Laravel Stream, it already has built-in login feature. However, the email column has to be case-insensitive. So we need to modify the logic by adding the following statement to the `boot()` method in `app/Providers/FortifyServiceProvider.php`

```php
public function boot()
{
    Fortify::authenticateUsing(function (Request $request) {
        $user = User::query()->firstWhere('email', 'ilike', $request->email);

        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
    });
  
  ...
    
}
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### 8.14. Backup

Using Laravel Backup from spatie https://github.com/spatie/laravel-backup.

The backup file will be stored in `storage/app/SWAP Backoffice` . Daily backup and auto delete in 7 days.

To run backup manually:

```
php artisan backup:run
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>