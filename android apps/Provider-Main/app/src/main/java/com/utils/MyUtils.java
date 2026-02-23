package com.utils;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.text.InputType;
import android.text.Spannable;
import android.text.SpannableString;
import android.text.style.ForegroundColorSpan;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.media3.common.util.UnstableApi;
import androidx.media3.database.StandaloneDatabaseProvider;
import androidx.media3.datasource.cache.NoOpCacheEvictor;
import androidx.media3.datasource.cache.SimpleCache;

import com.act.ChattingWindowActivity;
import com.general.files.Closure;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DialogLowWalletBalanceBinding;
import com.livechatinc.inappchat.ChatWindowConfiguration;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONObject;

import java.io.File;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.HashMap;
import java.util.Locale;

@UnstableApi
public class MyUtils {
    public static final int WEBVIEWPAYMENT = 7801;
    public static final int REFRESH_DATA_REQ_CODE = 7802;
    public static final int AUDIO_PERMISSION_REQ_CODE = 7803;
    @UnstableApi
    private static SimpleCache downloadCache;

    public static void setAppName(@NonNull Activity act, MTextView mTextView) {
        String txt = act.getString(R.string.APP_TYPE_NAME);
        Spannable spannableStr = new SpannableString(txt);
        int lastSpaceIndex = txt.lastIndexOf(" ");
        String lastWord = txt.substring(lastSpaceIndex + 1);
        spannableStr.setSpan(new ForegroundColorSpan(ContextCompat.getColor(act, R.color.appThemeColor_1)), txt.length() - lastWord.length(), txt.length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);
        mTextView.setText(spannableStr);
    }

    public static Locale getLocale() {
        return new Locale(MyApp.getInstance().getAppLevelGeneralFunc().retrieveValue(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY));
    }

    @UnstableApi
    public static SimpleCache getSimpleCache() {
        if (downloadCache == null) {
            downloadCache = new SimpleCache(new File(MyApp.getInstance().getCurrentAct().getExternalFilesDir(null), "downloads"),
                    new NoOpCacheEvictor(), new StandaloneDatabaseProvider(MyApp.getInstance().getCurrentAct()));
        }
        return downloadCache;
    }

    @SuppressLint("ClickableViewAccessibility")
    public static void editBoxMultiLine(MaterialEditText editText) {
        editText.setScrollBarStyle(View.SCROLLBARS_INSIDE_INSET);
        editText.setOverScrollMode(View.OVER_SCROLL_ALWAYS);
        editText.setOnTouchListener((v, event) -> {
            if (editText.hasFocus()) {
                v.getParent().requestDisallowInterceptTouchEvent(true);
                if ((event.getAction() & MotionEvent.ACTION_MASK) == MotionEvent.ACTION_SCROLL) {
                    v.getParent().requestDisallowInterceptTouchEvent(false);
                    return true;
                }
            }
            return false;
        });
        editText.setFloatingLabel(MaterialEditText.FLOATING_LABEL_NONE);
        editText.setSingleLine(false);
        editText.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        editText.setGravity(Gravity.TOP);
    }

    public static void setBounceAnimation(@NonNull Context context, @NonNull View view, int animView, @Nullable BounceAnimListener bounceAnimListener) {
        Animation anim = AnimationUtils.loadAnimation(context, animView);
        anim.setAnimationListener(new Animation.AnimationListener() {
            @Override
            public void onAnimationStart(Animation animation) {

            }

            @Override
            public void onAnimationEnd(Animation animation) {
                if (bounceAnimListener != null) {
                    bounceAnimListener.onAnimationFinished();
                }
            }

            @Override
            public void onAnimationRepeat(Animation animation) {

            }
        });
        view.startAnimation(anim);
    }

    public interface BounceAnimListener {
        void onAnimationFinished();
    }

    public static void openLiveChatActivity(Context context, GeneralFunctions generalFunc, JSONObject obj_userProfile) {

        String vName = generalFunc.getJsonValueStr("vName", obj_userProfile);
        String vLastName = generalFunc.getJsonValueStr("vLastName", obj_userProfile);

        String driverName = vName + " " + vLastName;
        String driverEmail = generalFunc.getJsonValueStr("vEmail", obj_userProfile);

        Utils.LIVE_CHAT_LICENCE_NUMBER = generalFunc.getJsonValueStr("LIVE_CHAT_LICENCE_NUMBER", obj_userProfile);
        HashMap<String, String> map = new HashMap<>();
        map.put("FNAME", vName);
        map.put("LNAME", vLastName);
        map.put("EMAIL", driverEmail);
        map.put("USERTYPE", Utils.userType);

        Intent intent = new Intent(context, ChattingWindowActivity.class);
        intent.putExtra(ChatWindowConfiguration.KEY_LICENCE_NUMBER, Utils.LIVE_CHAT_LICENCE_NUMBER);
        intent.putExtra(ChatWindowConfiguration.KEY_VISITOR_NAME, driverName);
        intent.putExtra(ChatWindowConfiguration.KEY_VISITOR_EMAIL, driverEmail);
        intent.putExtra(ChatWindowConfiguration.KEY_GROUP_ID, Utils.userType + "_" + generalFunc.getMemberId());

        intent.putExtra("myParam", map);
        if (Utils.checkText(Utils.LIVE_CHAT_LICENCE_NUMBER)) {
            context.startActivity(intent);
        }
    }

    public static void buildLowBalanceMessage(@NonNull Context context, @NonNull GeneralFunctions generalFunc, @NonNull JSONObject obj_userProfile, @NonNull String message, @Nullable final Closure closure) {

        AlertDialog.Builder builder = new AlertDialog.Builder(context);

        LayoutInflater inflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull DialogLowWalletBalanceBinding binding = DialogLowWalletBalanceBinding.inflate(inflater, null, false);

        binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOW_BALANCE"));
        binding.msgTxt.setText(message);

        binding.skipTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));

        if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash")) {
            binding.addNowTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        } else {
            binding.addNowTxtArea.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_NOW"));
        }

        //
        builder.setView(binding.getRoot());
        AlertDialog cashBalAlertDialog = builder.create();

        binding.skipTxtArea.setOnClickListener(view -> cashBalAlertDialog.dismiss());

        binding.addNowTxtArea.setOnClickListener(view -> {
            cashBalAlertDialog.dismiss();
            if (closure != null) {
                closure.exec();
            }
        });

        cashBalAlertDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(cashBalAlertDialog);
        cashBalAlertDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(context, R.drawable.all_roundcurve_card));
        if (!cashBalAlertDialog.isShowing() && !((Activity) context).isFinishing()) {
            cashBalAlertDialog.show();
        }
    }

    public static void configLangChanged(String value) {
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_LANG_CHANGED", value);
    }

    public static void configCurrencyChanged(String value) {
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_CURRENCY_CHANGED", value);
    }

    public static String isLangChanged() {
        String IS_LANG_CHANGED = MyApp.getInstance().getAppLevelGeneralFunc().retrieveValue("IS_LANG_CHANGED");
        return IS_LANG_CHANGED.trim().equalsIgnoreCase("") ? "No" : IS_LANG_CHANGED;
    }

    public static String isCurrencyChanged() {
        String IS_CURRENCY_CHANGED = MyApp.getInstance().getAppLevelGeneralFunc().retrieveValue("IS_CURRENCY_CHANGED");
        return IS_CURRENCY_CHANGED.trim().equalsIgnoreCase("") ? "No" : IS_CURRENCY_CHANGED;
    }

    public static void setPendingBookingsCount(String value) {
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("PENDING_BOOKING_COUNT", value);
    }

    public static void setIsVideoCallGenerated(String value) {
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_VIDEO_CALL_GENERATED", value);
    }

    public static String getPendingBookingsCount() {
        return MyApp.getInstance().getAppLevelGeneralFunc().retrieveValue("PENDING_BOOKING_COUNT");
    }

    public static String getIsVideoCallGenerated() {
        return MyApp.getInstance().getAppLevelGeneralFunc().retrieveValue("IS_VIDEO_CALL_GENERATED");
    }

    public static String mapToString(HashMap<String, String> map) {
        StringBuilder stringBuilder = new StringBuilder();

        for (String key : map.keySet()) {
            if (stringBuilder.length() > 0) {
                stringBuilder.append("&");
            }
            String value = map.get(key);
            try {
                stringBuilder.append((key != null ? URLEncoder.encode(key, "UTF-8") : ""));
                stringBuilder.append("=");
                stringBuilder.append(value != null ? URLEncoder.encode(value, "UTF-8") : "");
            } catch (UnsupportedEncodingException e) {
                throw new RuntimeException("This method requires UTF-8 encoding support", e);
            }
        }

        return stringBuilder.toString();
    }
}