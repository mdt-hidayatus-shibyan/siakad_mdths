# Flutter R8 / Proguard Rules
-dontwarn com.google.android.play.core.**
-dontwarn io.flutter.embedding.engine.deferredcomponents.**
-dontwarn androidx.**
-dontwarn com.dexterous.flutterlocalnotifications.**
-dontwarn xyz.luan.audioplayers.**

# Flutter Local Notifications Proguard Rules
-keep class com.dexterous.flutterlocalnotifications.** { *; }

# AndroidX Core Notification
-keep class androidx.core.app.NotificationManagerCompat { *; }

# Audio Players
-keep class xyz.luan.audioplayers.** { *; }

# Flutter and Plugins
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.util.** { *; }
-keep class io.flutter.view.** { *; }
-keep class io.flutter.** { *; }
-keep class io.flutter.plugins.** { *; }
