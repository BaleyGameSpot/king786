package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.MoreInstructionAdapter;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class UserPrefrenceActivity extends ParentActivity {


    RecyclerView moreInstruction;
    MoreInstructionAdapter moreInstructionAdapter;
    LinearLayout moreInstructionLayout;
    ArrayList<HashMap<String, String>> instructionsList;
    MTextView titleTxt;
    boolean isPreference = false;
    ImageView backImgView;
    ImageView iv_preferenceImg;
    CardView preferenceArea;
    MTextView preferenceImageTxt;
    String vImageDeliveryPref = "http";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_prefrence);


        titleTxt = findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        iv_preferenceImg = (ImageView) findViewById(R.id.iv_preferenceImg);
        preferenceArea = (CardView) findViewById(R.id.preferenceArea);
        preferenceImageTxt = (MTextView) findViewById(R.id.preferenceImageTxt);
        instructionsList = new ArrayList<>();
        moreInstructionLayout = findViewById(R.id.moreinstructionLyout);
        preferenceImageTxt.setText(generalFunc.retrieveLangLBl("view Preference Image", "LBL_VIEW_PREFERENCE_IMAGE"));

        moreInstruction = findViewById(R.id.moreinstuction);
        moreInstructionAdapter = new MoreInstructionAdapter(getActContext(), instructionsList, new MoreInstructionAdapter.OnItemCheckListener() {
            @Override
            public void onItemCheck(HashMap<String, String> map) {

            }

            @Override
            public void onItemUncheck(HashMap<String, String> item) {

            }

        });
        moreInstructionAdapter.showCheckBox(false);
        moreInstruction.setAdapter(moreInstructionAdapter);

        JSONObject DeliveryPreferences = null;
        try {
            DeliveryPreferences = new JSONObject(getIntent().getStringExtra("DeliveryPreferences"));
            getUserPreference(DeliveryPreferences);
            int imageWidth = (int) this.getResources().getDimension(R.dimen._90sdp);
            new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(getActContext(), vImageDeliveryPref, imageWidth, imageWidth)), iv_preferenceImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();


        } catch (JSONException e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        preferenceArea.setOnClickListener(v -> new ActUtils(getActContext()).openURL(vImageDeliveryPref));

        backImgView.setOnClickListener(v -> UserPrefrenceActivity.super.onBackPressed());

    }


    public void getUserPreference(JSONObject DeliveryPreferences) {
        JSONArray Data = generalFunc.getJsonArray("Data", DeliveryPreferences);
        if (Utils.checkText(generalFunc.getJsonValueStr("vTitle", DeliveryPreferences))) {
            titleTxt.setText(generalFunc.getJsonValueStr("vTitle", DeliveryPreferences));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("User Preferences", "LBL_USER_PREF"));
        }
        vImageDeliveryPref = generalFunc.getJsonValueStr("vImageDeliveryPref", DeliveryPreferences);
        preferenceArea.setVisibility(Utils.checkText(vImageDeliveryPref) ? View.VISIBLE : View.GONE);

        if (Data != null) {
            for (int i = 0; i < Data.length(); i++) {
                try {
                    JSONObject jsonObject = (JSONObject) Data.get(i);
                    String tTitle = generalFunc.getJsonValueStr("tTitle", jsonObject);
                    String tDescription = generalFunc.getJsonValueStr("tDescription", jsonObject);
                    String ePreferenceFor = generalFunc.getJsonValueStr("ePreferenceFor", jsonObject);
                    String eImageUpload = generalFunc.getJsonValueStr("eImageUpload", jsonObject);
                    String iDisplayOrder = generalFunc.getJsonValueStr("iDisplayOrder", jsonObject);
                    String eContactLess = generalFunc.getJsonValueStr("eContactLess", jsonObject);
                    String eStatus = generalFunc.getJsonValueStr("eStatus", jsonObject);
                    String iPreferenceId = generalFunc.getJsonValueStr("iPreferenceId", jsonObject);
                    HashMap<String, String> hashMap = new HashMap<>();

                    hashMap.put("tTitle", tTitle);
                    hashMap.put("tDescription", tDescription);
                    hashMap.put("ePreferenceFor", ePreferenceFor);
                    hashMap.put("eImageUpload", eImageUpload);
                    hashMap.put("iDisplayOrder", iDisplayOrder);
                    hashMap.put("eContactLess", eContactLess);
                    hashMap.put("eStatus", eStatus);
                    hashMap.put("iPreferenceId", iPreferenceId);
                    instructionsList.add(hashMap);

                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
                moreInstructionAdapter.notifyDataSetChanged();
            }
        }
    }


    public Context getActContext() {
        return UserPrefrenceActivity.this;
    }
}
