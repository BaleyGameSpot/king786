package com.utils;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.text.InputType;
import android.text.Spannable;
import android.text.SpannableString;
import android.text.TextUtils;
import android.text.style.ForegroundColorSpan;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.content.res.AppCompatResources;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.drawable.DrawableCompat;
import androidx.media3.common.util.UnstableApi;
import androidx.media3.database.StandaloneDatabaseProvider;
import androidx.media3.datasource.cache.NoOpCacheEvictor;
import androidx.media3.datasource.cache.SimpleCache;

import com.act.ChattingWindowActivity;
import com.act.MobileStegeActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetSubCategoryDataAllCategoryType;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DesignOpeningHrCellBinding;
import com.livechatinc.inappchat.ChatWindowConfiguration;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Locale;

public class MyUtils {
    public static final int WEBVIEWPAYMENT = 7801;
    public static final int REFRESH_DATA_REQ_CODE = 7802;
    public static final int AUDIO_PERMISSION_REQ_CODE = 7803;
    public static boolean isShadow = false;
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

    public static ArrayList<HashMap<String, String>> createArrayListJSONArray(@NonNull GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> hashMaps, @Nullable JSONArray dataArray) {
        if (dataArray != null && dataArray.length() > 0) {

            for (int i = 0; i < dataArray.length(); i++) {
                hashMaps.add(createHashMap(generalFunc, new HashMap<>(), generalFunc.getJsonObject(dataArray, i)));
            }
        }
        return hashMaps;
    }

    public static HashMap<String, String> createHashMap(@NonNull GeneralFunctions generalFunc, @NonNull HashMap<String, String> mapData, @NonNull JSONObject dataJObject) {
        Iterator<String> keysItr = dataJObject.keys();
        while (keysItr.hasNext()) {
            String key = keysItr.next();
            String value = generalFunc.getJsonValueStr(key, dataJObject);

            mapData.put(key, value);
        }
        return mapData;
    }

    public static JSONObject createJsonObject(HashMap<String, String> mapData, JSONObject jsonObject) {
        for (int i = 0; i < mapData.size(); i++) {
            for (String mapKey : mapData.keySet()) {
                try {
                    jsonObject.put(mapKey, mapData.get(mapKey));
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }
        return jsonObject;
    }

    public static Integer getNumOfColumns(Activity mActivity) {
        try {
            DisplayMetrics displayMetrics = mActivity.getResources().getDisplayMetrics();
            /*float dpWidth = (displayMetrics.widthPixels - Utils.dipToPixels(getActContext(), 10)) / displayMetrics.density;
            int margin_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._10sdp) * 2;
            int menuItem_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._5sdp) * 2;*/
            int margin_int_value = mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp);
            int menuItem_int_value = mActivity.getResources().getDimensionPixelSize(R.dimen._5sdp);
            int catWidth_int_value = mActivity.getResources().getDimensionPixelSize(R.dimen.category_grid_size_more);
            int screenWidth_int_value = displayMetrics.widthPixels - margin_int_value - menuItem_int_value;
            return (int) (screenWidth_int_value / catWidth_int_value);
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
        return -1;
    }

    public static void manageAddressHeader(Activity mActivity, GeneralFunctions generalFunc, MTextView headerTxtView, int tintColor) {
        Drawable locationDrawable = AppCompatResources.getDrawable(mActivity, R.drawable.ic_place_address_fill);
        Drawable arrowDrawable = AppCompatResources.getDrawable(mActivity, R.drawable.ic_down_arrow_header);

        Drawable wLocDrawable = null;
        if (locationDrawable != null) {
            wLocDrawable = DrawableCompat.wrap(locationDrawable);
            DrawableCompat.setTint(wLocDrawable.mutate(), ContextCompat.getColor(mActivity, tintColor));
        }

        Drawable wArrowDrawable = null;
        if (arrowDrawable != null) {
            wArrowDrawable = DrawableCompat.wrap(arrowDrawable);
            DrawableCompat.setTint(wArrowDrawable.mutate(), ContextCompat.getColor(mActivity, tintColor));
        }

        if (generalFunc.isRTLmode()) {
            headerTxtView.setCompoundDrawablesWithIntrinsicBounds(wArrowDrawable, null, wLocDrawable, null);
        } else {
            headerTxtView.setCompoundDrawablesWithIntrinsicBounds(wLocDrawable, null, wArrowDrawable, null);
        }
    }

    @SuppressLint("InflateParams")
    public static View addFareDetailRow(Context context, GeneralFunctions generalFunc, String rName, String rValue, boolean isLast) {
        View convertView;
        if (rName.equalsIgnoreCase("eDisplaySeperator")) {
            convertView = new View(context);
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(context, 1));
            params.setMargins(0, Utils.dipToPixels(context, context.getResources().getDimensionPixelSize(R.dimen._3sdp)), 0, Utils.dipToPixels(context, context.getResources().getDimensionPixelSize(R.dimen._2sdp)));
            //params.setMarginStart(Utils.dipToPixels(getActContext(), 10));
            //params.setMarginEnd(Utils.dipToPixels(getActContext(), 10));
            convertView.setLayoutParams(params);
            convertView.setBackgroundColor(Color.parseColor("#DEDEDE"));
        } else {
            LayoutInflater infalInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.design_fare_breakdown_row, null);

            MTextView titleHTxt = convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = convertView.findViewById(R.id.titleVTxt);

            titleHTxt.setText(generalFunc.convertNumberWithRTL(rName));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(rValue));

            if (!Utils.checkText(rValue)) {
                titleHTxt.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
                titleVTxt.setVisibility(View.GONE);
            }

            if (isLast) {
                titleHTxt.setTextColor(context.getResources().getColor(R.color.text23Pro_Dark));
                titleHTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16);

                titleVTxt.setTextColor(context.getResources().getColor(R.color.appThemeColor_1));
                titleVTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16);
            }
        }
        return convertView;
    }

    public static void timeSlotRow(Context context, GeneralFunctions generalFunc, LinearLayout llView, ArrayList<HashMap<String, String>> slotsArray, boolean isDisplaySeparator) {
        for (int i = 0; i < slotsArray.size(); i++) {

            LayoutInflater itemCartInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull DesignOpeningHrCellBinding binding = DesignOpeningHrCellBinding.inflate(itemCartInflater, llView, false);
            binding.timeHTxt.setText(generalFunc.convertNumberWithRTL(slotsArray.get(i).get("DayName")));
            binding.timeVTxt.setText(generalFunc.convertNumberWithRTL(slotsArray.get(i).get("DayTime")));
            llView.addView(binding.getRoot());

            if (isDisplaySeparator && (i + 1) != slotsArray.size()) {
                View convertView = new View(context);
                LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(context, 1));
                //params.setMarginStart(Utils.dipToPixels(getActContext(), 10));
                //params.setMarginEnd(Utils.dipToPixels(getActContext(), 10));
                convertView.setLayoutParams(params);
                convertView.setBackgroundColor(Color.parseColor("#DEDEDE"));
                llView.addView(convertView);
            }
        }
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

    public static void openMobileStageActivity(Context context) {
        Bundle bundle = new Bundle();
        bundle.putString("type", "login");
        new ActUtils(context).startActWithData(MobileStegeActivity.class, bundle);
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

    //use for execute local store for Uberx & Bidding all sub Category list
    public static void updateSubcategoryForAllCategoryType(@NonNull GeneralFunctions generalFunc, @NonNull String message, @NonNull String latitude, @NonNull String longitude, boolean isBidding) {
        JSONArray serviceArrayLbl = generalFunc.getJsonArray("SubCategories", message);
        if (serviceArrayLbl != null && serviceArrayLbl.length() > 0) {
            ArrayList<String> serviceMap = new ArrayList<>();
            for (int i = 0; i < serviceArrayLbl.length(); i++) {
                JSONObject serviceObj = generalFunc.getJsonObject(serviceArrayLbl, i);
                if (isBidding)
                    serviceMap.add(generalFunc.getJsonValue("iBiddingId", serviceObj.toString()));
                else
                    serviceMap.add(generalFunc.getJsonValue("iVehicleCategoryId", serviceObj.toString()));
            }
            new GetSubCategoryDataAllCategoryType(MyApp.getInstance().getApplicationContext(), generalFunc, TextUtils.join(",", serviceMap), latitude, longitude, isBidding);
        }
    }

    public static void ShowIntroScreens() {
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_ADDSTOP_INFO_OPEN", "No");
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_BIDDING_INFO_OPEN", "No");
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_RIDERESERVE_INFO_OPEN", "No");
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_RIDEPOOL_INFO_OPEN", "No");
        MyApp.getInstance().getAppLevelGeneralFunc().storeData("IS_RIDE_INFO_OPEN", "No");
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