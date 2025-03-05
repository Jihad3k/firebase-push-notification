# Firebase Push Notification Setup in Android Studio (Java)

This guide will help you set up Firebase Push Notifications in your Android Studio project. We will use Firebase Cloud Messaging (FCM) to send push notifications. The dependencies specified are:

- Glide (for image loading): `implementation 'com.github.bumptech.glide:glide:4.15.0'`
- Glide Compiler: `annotationProcessor 'com.github.bumptech.glide:compiler:4.15.0'`
- Firebase Messaging: `implementation 'com.google.firebase:firebase-messaging:23.1.0'`

---

## Prerequisites

1. **Android Studio** installed on your system.
2. **Firebase Project** created on Firebase Console (https://console.firebase.google.com/).
3. **Firebase SDK** added to your Android project.

---

## Step 1: Add Firebase to Your Android Project

1. Open [Firebase Console](https://console.firebase.google.com/).
2. Create a new Firebase project or open an existing one.
3. Click **Add Project** and follow the steps.
4. After your Firebase project is set up, click on the **Android icon** to add Firebase to your Android app.
5. Enter your app's **package name** and the **SHA-1 key** (you can generate it using `keytool` or from Android Studio).
6. Download the `google-services.json` file provided and place it in the `app/` directory of your Android project.
7. Click **Next** and follow the instructions to complete the Firebase setup.

---

## Step 2: Add Required Dependencies

1. Open your `build.gradle (Project)` file and add the Google services classpath to the `dependencies` section.

```gradle
// build.gradle (Project)
buildscript {
    repositories {
        google()  // Google repository
        mavenCentral()  // Maven central repository
    }
    dependencies {
        classpath 'com.android.tools.build:gradle:7.4.1'  // Or latest version
        classpath 'com.google.gms:google-services:4.3.15'  // Google Services plugin
    }
}
```

2. Open the `build.gradle (Module: app)` file and add the following dependencies:

```gradle
// build.gradle (Module: app)
plugins {
    id 'com.android.application'
    id 'com.google.gms.google-services'  // Apply Google services plugin
}

android {
    compileSdkVersion 33
    defaultConfig {
        applicationId "com.example.pushnotification"
        minSdkVersion 21
        targetSdkVersion 33
        versionCode 1
        versionName "1.0"
    }
    // Other configurations...
}

dependencies {
    implementation 'com.github.bumptech.glide:glide:4.15.0'  // Glide dependency
    annotationProcessor 'com.github.bumptech.glide:compiler:4.15.0'  // Glide compiler

    implementation 'com.google.firebase:firebase-messaging:23.1.0'  // Firebase Messaging dependency
    implementation 'com.google.firebase:firebase-analytics'  // Optional: Firebase Analytics

    // Other dependencies...
}
```

---

## Step 3: Sync Gradle

Click on **Sync Now** in the top-right corner to sync the Gradle files and ensure all dependencies are properly downloaded.

---

## Step 4: Enable Firebase Cloud Messaging in Firebase Console

1. Go to the **Firebase Console**.
2. Click on **Cloud Messaging** under the **Grow** section in the left sidebar.
3. Enable Firebase Cloud Messaging for your project.

---

## Step 5: Set Up Firebase Messaging Service

Create a custom Firebase messaging service class to handle incoming push notifications.

1. Create a new Java class called `MyFirebaseMessagingService.java` inside the `src` folder.

```java
public class MyFirebaseMessagingService extends FirebaseMessagingService {

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {

        // Check if the notification is not null in the remote message
        if (remoteMessage.getNotification() != null) {
            // If notification is present, call processFCMNotification()
            processFCMNotification(remoteMessage);
        } else {
            // If no notification, use data from the remote message
            String notificationTitle = remoteMessage.getData().get("title");
            String notificationBody = remoteMessage.getData().get("body");
            String imageUrl = remoteMessage.getData().get("imageUrl");
            String actionUrl = remoteMessage.getData().get("actionUrl");

            sendNotification(notificationTitle, notificationBody, imageUrl, actionUrl);
        }
    }

    // Method to process the notification from FCM
    private void processFCMNotification(RemoteMessage remoteMessage) {
        String notificationBody = remoteMessage.getNotification().getBody();
        String notificationTitle = remoteMessage.getNotification().getTitle();

        // You can pass this data to sendNotification() for further processing
        sendNotification(notificationTitle, notificationBody, null, null);
    }

    public void sendNotification(String notificationTitle, String notificationBody, String imageUrl, String actionUrl) {
        // Create an Intent for MainActivity
        Intent intent = new Intent(this, MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);

        // Use PendingIntent to handle click event
        PendingIntent pendingIntent;
        if (TextUtils.isEmpty(actionUrl)) {
            // If actionUrl is null or empty, open MainActivity
            pendingIntent = PendingIntent.getActivity(this, 0 /* Request code */, intent, PendingIntent.FLAG_IMMUTABLE);
        } else {
            // Otherwise, open the URL in the browser
            Intent browserIntent = new Intent(Intent.ACTION_VIEW, Uri.parse(actionUrl));
            pendingIntent = PendingIntent.getActivity(this, 0 /* Request code */, browserIntent, PendingIntent.FLAG_IMMUTABLE);
        }

        // Notification channel ID
        String channelId = "fcm_default_channel";

        // Default notification sound
        Uri defaultSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);

        // Create a notification builder
        NotificationCompat.Builder notificationBuilder = new NotificationCompat.Builder(this, channelId)
                .setSmallIcon(R.drawable.ic_noti)
                .setContentTitle(notificationTitle)
                .setContentText(notificationBody)
                .setBadgeIconType(R.drawable.ic_noti)
                .setAutoCancel(true)
                .setSound(defaultSoundUri)
                .setContentIntent(pendingIntent)
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .setDefaults(NotificationCompat.DEFAULT_ALL)
                .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                .setCategory(NotificationCompat.CATEGORY_MESSAGE)
                .setColor(Color.parseColor("#113946"))
                .setGroup("group_key_notifications")
                .setGroupSummary(true)
                .setStyle(new NotificationCompat.BigTextStyle()
                        .bigText(notificationBody)
                        .setBigContentTitle(notificationTitle)
                        .setSummaryText(""))
                .setBadgeIconType(NotificationCompat.BADGE_ICON_SMALL);

        // Load the image using Glide and add it to the notification
        if (!TextUtils.isEmpty(imageUrl)) {
            Glide.with(this)
                    .asBitmap()
                    .load(imageUrl)
                    .into(new CustomTarget<Bitmap>() {
                        @Override
                        public void onResourceReady(Bitmap resource, Transition<? super Bitmap> transition) {
                            // Set the large icon to show image in notification
                            notificationBuilder.setLargeIcon(resource);
                            notificationBuilder.setStyle(new NotificationCompat.BigPictureStyle()
                                    .bigPicture(resource)
                                    .bigLargeIcon((Bitmap) null));  // Optional, hides the small icon
                            NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
                            notificationManager.notify(0, notificationBuilder.build());
                        }

                        @Override
                        public void onLoadCleared(Drawable placeholder) {
                            // Handle cleanup if needed
                        }
                    });
        } else {
            // If no image URL, show notification without image
            NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
            notificationManager.notify(0, notificationBuilder.build());
        }

        // Since Android Oreo (API 26) requires notification channels
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(channelId,
                    "App Notifications",
                    NotificationManager.IMPORTANCE_HIGH);
            channel.enableLights(true);
            channel.setLightColor(Color.BLUE);
            channel.enableVibration(true);
            channel.setVibrationPattern(new long[]{0, 250, 250, 250});
            channel.setDescription("Important notifications from the app");
            channel.setShowBadge(true);
            channel.setLockscreenVisibility(NotificationCompat.VISIBILITY_PUBLIC);
            NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
            notificationManager.createNotificationChannel(channel);
        }

    }
}

```

---

## Step 6: Register the Service in `AndroidManifest.xml`

Now, register the Firebase messaging service in your `AndroidManifest.xml`.

```xml
<application
    android:name=".MyApplication"
    android:label="@string/app_name"
    android:icon="@mipmap/ic_launcher">
    
    <!-- Register Firebase messaging service -->
    <service
        android:name=".MyFirebaseMessagingService"
        android:exported="false">
        <intent-filter>
            <action android:name="com.google.firebase.MESSAGING_EVENT" />
        </intent-filter>
    </service>

    <!-- Other configurations -->
</application>
```

---

## Step 7: Request Permissions (If Required)

In Android 13 (API level 33) and higher, you'll need to request the `POST_NOTIFICATIONS` permission to allow your app to receive push notifications.

Add this permission to the `AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>
<uses-permission android:name="android.permission.ACCESS_WIFI_STATE"/>
```

---

## Step 8: Initialize Firebase in `MainActivity.java`

Ensure Firebase is initialized in your app. You can initialize Firebase in your `MainActivity.java` or `Application` class.

```java
public void initFirebaseToken() {
        FirebaseMessaging.getInstance().subscribeToTopic("allDevices")
                .addOnCompleteListener(new OnCompleteListener<Void>() {
                    @Override
                    public void onComplete(@NonNull Task<Void> task) {
                        String msg = "Subscribed to topic";
                        if (!task.isSuccessful()) {
                            msg = "Subscription failed";
                        }
                        Log.d("token", msg);
                    }
                });
    }

    // Declare the launcher at the top of your Activity/Fragment:
    private final ActivityResultLauncher<String> requestPermissionLauncher =
            registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
                if (isGranted) {
                    // FCM SDK (and your app) can post notifications.
                } else {
                    // TODO: Inform user that that your app will not show notifications.
                }
            });

    private void askNotificationPermission() {
        // This is only necessary for API level >= 33 (TIRAMISU)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.POST_NOTIFICATIONS) ==
                    PackageManager.PERMISSION_GRANTED) {
                // FCM SDK (and your app) can post notifications.
            } else if (shouldShowRequestPermissionRationale(android.Manifest.permission.POST_NOTIFICATIONS)) {
                // Show rationale for permission
                new AlertDialog.Builder(MainActivity.this)
                        .setTitle("Notification Permission")
                        .setMessage("Please allow notification permission to receive updates.")
                        .setPositiveButton("OK", new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                // After user clicks "OK", request the permission again
                                requestPermissionLauncher.launch(android.Manifest.permission.POST_NOTIFICATIONS);
                            }
                        })
                        .setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                // User chose to cancel, no further action needed
                            }
                        })
                        .create()
                        .show();
            } else {
                // Directly ask for the permission
                requestPermissionLauncher.launch(android.Manifest.permission.POST_NOTIFICATIONS);
            }
        }
    }
```
## Call The Method In MainActivity
```java
        askNotificationPermission();
        initFirebaseToken();
```
---
## Step 9: Send a Test Push Notification

1. Go to the **Firebase Notification Panel**.
2. Click **Push Notification** under the **Dashboard** section.
3. Click on **Send your first message**.
4. Enter a message title and body.
5. Click **Send Message**.

You should now receive the push notification on your Android device.

---

## Conclusion

Your app is now set up to receive push notifications using Firebase Cloud Messaging (FCM) with the specified dependencies (Glide, Firebase Messaging). 

You can customize the `MyFirebaseMessagingService` class further to display notifications, handle images, or integrate it with other services as needed.

---

Feel free to reach out if you have any questions!
