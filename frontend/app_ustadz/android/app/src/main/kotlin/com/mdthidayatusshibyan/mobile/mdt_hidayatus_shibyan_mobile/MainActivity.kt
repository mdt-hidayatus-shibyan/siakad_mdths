package com.mdthidayatusshibyan.mobile.mdt_hidayatus_shibyan_mobile

import android.app.AlarmManager
import android.app.NotificationManager
import android.content.Context
import android.content.Intent
import android.media.AudioAttributes
import android.media.AudioManager
import android.media.MediaPlayer
import android.net.Uri
import android.os.Build
import android.os.PowerManager
import android.provider.Settings
import androidx.annotation.NonNull
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val CHANNEL = "com.mdthidayatusshibyan.app/settings"
    private var mediaPlayer: MediaPlayer? = null

    override fun onCreate(savedInstanceState: android.os.Bundle?) {
        super.onCreate(savedInstanceState)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
            setShowWhenLocked(true)
            setTurnScreenOn(true)
        } else {
            @Suppress("DEPRECATION")
            window.addFlags(
                android.view.WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED or
                android.view.WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON
            )
        }
    }

    override fun configureFlutterEngine(@NonNull flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL).setMethodCallHandler { call, result ->
            when (call.method) {
                "checkPermissionsStatus" -> {
                    val status = checkPermissionsStatus()
                    result.success(status)
                }
                "requestBatteryExemption" -> {
                    val requested = requestBatteryExemption()
                    result.success(requested)
                }
                "playNativeAlarmSound" -> {
                    val repeats = call.argument<Int>("repeats") ?: 1
                    playNativeAlarmSound(repeats)
                    result.success(true)
                }
                "stopNativeAlarmSound" -> {
                    stopNativeAlarmSound()
                    result.success(true)
                }
                "openAppSettings" -> {
                    openAppSettings()
                    result.success(true)
                }
                "openNotificationSettings" -> {
                    openNotificationSettings()
                    result.success(true)
                }
                "openExactAlarmSettings" -> {
                    openExactAlarmSettings()
                    result.success(true)
                }
                "openBatterySettings" -> {
                    openBatterySettings()
                    result.success(true)
                }
                else -> {
                    result.notImplemented()
                }
            }
        }
    }

    private fun playNativeAlarmSound(repeats: Int) {
        try {
            stopNativeAlarmSound()
            val soundResId = resources.getIdentifier("school_bell", "raw", packageName)
            if (soundResId != 0) {
                mediaPlayer = MediaPlayer.create(this, soundResId)?.apply {
                    setAudioAttributes(
                        AudioAttributes.Builder()
                            .setUsage(AudioAttributes.USAGE_ALARM)
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .setLegacyStreamType(AudioManager.STREAM_ALARM)
                            .build()
                    )
                    setVolume(1.0f, 1.0f)
                    var count = 0
                    setOnCompletionListener { mp ->
                        count++
                        if (count < repeats) {
                            mp.seekTo(0)
                            mp.start()
                        } else {
                            stopNativeAlarmSound()
                        }
                    }
                    start()
                }
            }
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }

    private fun stopNativeAlarmSound() {
        try {
            mediaPlayer?.stop()
            mediaPlayer?.release()
            mediaPlayer = null
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }

    private fun checkPermissionsStatus(): Map<String, Boolean> {
        val nm = getSystemService(Context.NOTIFICATION_SERVICE) as? NotificationManager
        val notifications = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            nm?.areNotificationsEnabled() ?: true
        } else {
            true
        }

        val pm = getSystemService(Context.POWER_SERVICE) as? PowerManager
        val batteryIgnored = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            pm?.isIgnoringBatteryOptimizations(packageName) ?: true
        } else {
            true
        }

        val am = getSystemService(Context.ALARM_SERVICE) as? AlarmManager
        val exactAlarm = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            am?.canScheduleExactAlarms() ?: true
        } else {
            true
        }

        return mapOf(
            "notifications" to notifications,
            "batteryIgnored" to batteryIgnored,
            "exactAlarm" to exactAlarm
        )
    }

    private fun requestBatteryExemption(): Boolean {
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                val pm = getSystemService(Context.POWER_SERVICE) as? PowerManager
                if (pm != null && !pm.isIgnoringBatteryOptimizations(packageName)) {
                    val intent = Intent(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS).apply {
                        data = Uri.parse("package:$packageName")
                        addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                    }
                    startActivity(intent)
                    return true
                }
            }
        } catch (e: Exception) {
            openBatterySettings()
        }
        return false
    }

    private fun openAppSettings() {
        try {
            val intent = Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS).apply {
                data = Uri.fromParts("package", packageName, null)
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            }
            startActivity(intent)
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }

    private fun openNotificationSettings() {
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                val intent = Intent(Settings.ACTION_APP_NOTIFICATION_SETTINGS).apply {
                    putExtra(Settings.EXTRA_APP_PACKAGE, packageName)
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                startActivity(intent)
            } else {
                openAppSettings()
            }
        } catch (e: Exception) {
            openAppSettings()
        }
    }

    private fun openExactAlarmSettings() {
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                val intent = Intent(Settings.ACTION_REQUEST_SCHEDULE_EXACT_ALARM).apply {
                    data = Uri.fromParts("package", packageName, null)
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                startActivity(intent)
            } else {
                openAppSettings()
            }
        } catch (e: Exception) {
            openAppSettings()
        }
    }

    private fun openBatterySettings() {
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                val intent = Intent(Settings.ACTION_IGNORE_BATTERY_OPTIMIZATION_SETTINGS).apply {
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                startActivity(intent)
            } else {
                openAppSettings()
            }
        } catch (e: Exception) {
            openAppSettings()
        }
    }

    override fun onDestroy() {
        stopNativeAlarmSound()
        super.onDestroy()
    }
}
