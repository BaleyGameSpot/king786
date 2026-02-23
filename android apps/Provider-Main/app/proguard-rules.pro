# 16KB Page Size Optimization Rules
# These rules are specifically optimized for Android 35+ with 16KB page size support

# Basic ProGuard configuration
-keepattributes *Annotation*
-keepattributes Exceptions,InnerClasses,Signature,Deprecated,SourceFile,LineNumberTable,EnclosingMethod
-keepparameternames
-renamesourcefileattribute SourceFile

# 16KB Page Size Memory Optimizations
-optimizations !code/simplification/arithmetic,!field/*,!class/merging/*
-allowaccessmodification
-repackageclasses ''
-overloadaggressively

# Core Android & Java classes
-keep class java.lang.** { *; }
-keep class org.** { *; }
-keep class org.joda.convert.** { *; }
-keep class org.joda.time.** { *; }

# Keep public classes and methods
-keep public class * {
    public protected *;
}

-keepclassmembernames class * {
    java.lang.Class class$(java.lang.String);
    java.lang.Class class$(java.lang.String, boolean);
}

# Native methods - Critical for 16KB page size
-keepclasseswithmembernames,includedescriptorclasses class * {
    native <methods>;
}

# Serialization support
-keepclassmembers class * implements java.io.Serializable {
    static final long serialVersionUID;
    private static final java.io.ObjectStreamField[] serialPersistentFields;
    private void writeObject(java.io.ObjectOutputStream);
    private void readObject(java.io.ObjectInputStream);
    java.lang.Object writeReplace();
    java.lang.Object readResolve();
}

# ============================================================================
# 16KB PAGE SIZE SPECIFIC OPTIMIZATIONS
# ============================================================================

# Google Maps SDK - 16KB Page Size Support
-keep class com.google.android.gms.maps.** { *; }
-dontwarn com.google.android.gms.maps.**
-keep class com.google.android.gms.location.** { *; }
-dontwarn com.google.android.gms.location.**
-keep class com.google.android.gms.common.** { *; }
-dontwarn com.google.android.gms.common.**
-keep class com.google.android.gms.ads.** { *; }
-dontwarn com.google.android.gms.ads.**

# Facebook Audience Network - 16KB compatibility
-keep class com.facebook.ads.** { *; }
-dontwarn com.facebook.ads.**
-keep interface com.facebook.ads.** { *; }

# Firebase SDK - Memory optimized rules
-keep class com.google.firebase.** { *; }
-dontwarn com.google.firebase.**
-keep class com.google.android.gms.** { *; }
-dontwarn com.google.android.gms.**

# OkHttp & Retrofit - Network optimization
-keep class okhttp3.** { *; }
-dontwarn okhttp3.**
-dontwarn okio.**
-keep class retrofit2.** { *; }
-dontwarn retrofit2.**

# Gson - JSON serialization
-keep class com.google.gson.** { *; }
-dontwarn com.google.gson.**
-keepclassmembers,allowobfuscation class * {
    @com.google.gson.annotations.SerializedName <fields>;
}

# ============================================================================
# MEMORY & PERFORMANCE OPTIMIZATIONS FOR 16KB PAGE SIZE
# ============================================================================

# Glide Image Loading - Memory optimized
-keep public class * implements com.bumptech.glide.module.GlideModule
-keep public class * extends com.bumptech.glide.module.AppGlideModule
-keep public enum com.bumptech.glide.load.ImageHeaderParser$** {
    **[] $VALUES;
    public *;
}
-keep class com.bumptech.glide.** { *; }
-dontwarn com.bumptech.glide.**

# Picasso Image Loading
-keep class com.squareup.picasso.** { *; }
-dontwarn com.squareup.picasso.**

# Lottie Animations - Memory optimized
-keep class com.airbnb.lottie.** { *; }
-dontwarn com.airbnb.lottie.**

# MPAndroidChart
-keep class com.github.mikephil.charting.** { *; }
-dontwarn com.github.mikephil.charting.**

# ============================================================================
# NATIVE LIBRARIES & JNI SUPPORT (Critical for 16KB)
# ============================================================================

# WebRTC & Communication libraries
-keep class org.webrtc.** { *; }
-dontwarn org.webrtc.**
-keep class com.sinch.** { *; }
-keep interface com.sinch.** { *; }

# HaishinKit streaming library
-keep class com.haishinkit.** { *; }
-dontwarn com.haishinkit.**

# ExoPlayer Media libraries
-keep class com.google.android.exoplayer2.** { *; }
-dontwarn com.google.android.exoplayer2.**
-keep class androidx.media3.** { *; }
-dontwarn androidx.media3.**

# ============================================================================
# UI COMPONENTS & CUSTOM VIEWS
# ============================================================================

# Material Design Components
-keep class com.google.android.material.** { *; }
-dontwarn com.google.android.material.**

# AndroidX Components
-keep class androidx.** { *; }
-dontwarn androidx.**

# Custom UI Components
-keep class com.view.** { *; }
-keep class com.getbase.floatingactionbutton.** { *; }
-keep class com.kyleduo.switchbutton.** { *; }

# ============================================================================
# KOTLIN & REFLECTION SUPPORT
# ============================================================================

# Kotlin specific rules
-keep class kotlin.** { *; }
-keep class kotlin.reflect.** { *; }
-dontwarn kotlin.**
-keepclassmembers class **$WhenMappings {
    <fields>;
}
-keepclassmembers class kotlin.Metadata {
    public <methods>;
}

# ============================================================================
# GENERAL WARNINGS SUPPRESSION
# ============================================================================

# Suppress common warnings that don't affect 16KB functionality
-dontwarn java.lang.invoke.**
-dontwarn javax.annotation.**
-dontwarn org.xmlpull.v1.**
-dontwarn javax.annotation.Nullable
-dontwarn javax.annotation.ParametersAreNonnullByDefault
-dontwarn javax.**
-dontwarn lombok.**
-dontwarn org.apache.**
-dontwarn com.squareup.**
-dontwarn **retrofit**
-dontwarn org.apache.http.annotation.**
-dontwarn com.huawei.**

# ============================================================================
# FINAL OPTIMIZATIONS FOR 16KB PAGE SIZE
# ============================================================================

# Enable aggressive optimizations
-dontskipnonpubliclibraryclassmembers
-dontskipnonpubliclibraryclasses
-adaptclassstrings

# Memory alignment optimizations
-optimizationpasses 5
-allowaccessmodification
-mergeinterfacesaggressively

# Ignore warnings that don't affect functionality
-ignorewarnings

# Keep line numbers for crash reporting
-keepattributes SourceFile,LineNumberTable

# Remove debug information in release builds
-assumenosideeffects class android.util.Log {
    public static boolean isLoggable(java.lang.String, int);
    public static int v(...);
    public static int i(...);
    public static int w(...);
    public static int d(...);
    public static int e(...);
}