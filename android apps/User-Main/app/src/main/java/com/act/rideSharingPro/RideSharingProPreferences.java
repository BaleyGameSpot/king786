package com.act.rideSharingPro;

import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideSharingPreferencesBinding;
import com.buddyverse.main.databinding.ItemRideProDynamicViewBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.concurrent.atomic.AtomicInteger;

public class RideSharingProPreferences extends ParentActivity {
    private ActivityRideSharingPreferencesBinding binding;
    private final ArrayList<JSONArray> optionList = new ArrayList<>();
    private MButton saveBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_sharing_preferences);

        initialization();
        getPreferencesData();
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_YOU_RIDE_SHARE_TEXT"));

        saveBtn = ((MaterialRippleLayout) binding.saveBtn).getChildView();
        saveBtn.setId(Utils.generateViewId());
        addToClickHandler(saveBtn);
        saveBtn.setText(generalFunc.retrieveLangLBl("", "LBL_Save"));
    }

    private void getPreferencesData() {
        binding.mainDataArea.setVisibility(View.GONE);
        binding.loadingBar.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getTravelPreferences");

        ApiHandler.execute(this, parameters, responseString -> {
            binding.mainDataArea.setVisibility(View.VISIBLE);
            binding.loadingBar.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    dynamicView(generalFunc.getJsonArray(message));
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError(true);
            }
        });
    }

    private void dynamicView(@Nullable JSONArray jsonArray) {
        if (binding.dynamicPreferencesView.getChildCount() > 0) {
            binding.dynamicPreferencesView.removeAllViewsInLayout();
        }
        optionList.clear();
        if (jsonArray != null) {
            for (int k = 0; k < jsonArray.length(); k++) {
                LayoutInflater itemCartInflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                @NonNull ItemRideProDynamicViewBinding bindingItem = ItemRideProDynamicViewBinding.inflate(itemCartInflater, binding.dynamicPreferencesView, false);

                JSONObject mItemObj = generalFunc.getJsonObject(jsonArray, k);

                bindingItem.titleHTxt.setText(generalFunc.getJsonValueStr("vCategory", mItemObj));
                bindingItem.selectedTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_AND_SELECT"));

                optionList.add(k, generalFunc.getJsonArray("Option", mItemObj));

                AtomicInteger selCurrentPosition = new AtomicInteger(-1);
                ArrayList<String> arrayList = new ArrayList<>();

                JSONArray OptionArr = optionList.get(k);
                if (OptionArr != null) {
                    for (int j = 0; j < OptionArr.length(); j++) {
                        JSONObject optionObj = generalFunc.getJsonObject(OptionArr, j);
                        String mTitle = generalFunc.getJsonValueStr("Title", optionObj);
                        arrayList.add(mTitle);
                        if (generalFunc.getJsonValueStr("Selected", optionObj).equalsIgnoreCase("Yes")) {
                            selCurrentPosition.set(j);
                            bindingItem.selectedTxt.setText(mTitle);
                        }
                    }
                }

                bindingItem.selectedTxt.setOnClickListener(view -> {
                    OpenListView.getInstance(this, generalFunc.getJsonValueStr("vCategory", mItemObj), arrayList, OpenListView.OpenDirection.CENTER, true, true, position -> {
                        selCurrentPosition.set(position);
                        bindingItem.selectedTxt.setText(arrayList.get(position));

                        try {
                            if (OptionArr != null) {
                                for (int j = 0; j < OptionArr.length(); j++) {
                                    JSONObject optionObj = generalFunc.getJsonObject(OptionArr, j);
                                    optionObj.put("Selected", j == position ? "Yes" : "No");
                                    OptionArr.put(j, optionObj);
                                }
                            }
                        } catch (JSONException e) {
                            throw new RuntimeException(e);
                        }
                    }).show(selCurrentPosition.get(), "Title");
                });

                binding.dynamicPreferencesView.addView(bindingItem.getRoot());
            }
        }
    }

    private void updatePreferences() {
        String tPreferencesIds = "";
        for (int j = 0; j < optionList.size(); j++) {
            JSONArray OptionArr = optionList.get(j);
            for (int k = 0; k < OptionArr.length(); k++) {
                JSONObject optionObj = generalFunc.getJsonObject(OptionArr, k);
                if (generalFunc.getJsonValueStr("Selected", optionObj).equalsIgnoreCase("Yes")) {
                    String tPrefId = generalFunc.getJsonValueStr("TravelPreferencesId", optionObj);
                    if (tPreferencesIds.equals("")) {
                        tPreferencesIds = tPrefId;
                    } else {
                        tPreferencesIds = tPreferencesIds + "," + tPrefId;
                    }
                }
            }
        }

        if (!Utils.checkText(tPreferencesIds)) {
            generalFunc.showMessage(binding.mainDataArea, generalFunc.retrieveLangLBl("", "LBL_PLEASE_SELECT"));
            return;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateTravelPreferences");
        parameters.put("TravelPreferencesId", tPreferencesIds);

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            } else {
                generalFunc.showError();
            }
        });
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            finish();
        } else if (i == saveBtn.getId()) {
            updatePreferences();
        }
    }
}