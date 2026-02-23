package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;

import androidx.activity.OnBackPressedCallback;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fragments.BusinessProfileIntroFragment;
import com.fragments.BusinessProfileListFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityBusinessProfileBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class BusinessProfileActivity extends ParentActivity implements GeneralFunctions.OnAlertButtonClickListener {

    private BusinessProfileIntroFragment businessProfileIntroFragment;
    private BusinessProfileListFragment businessProfileListFragment;
    private ArrayList<HashMap<String, String>> listdata = new ArrayList<>();
    private ActivityBusinessProfileBinding binding;
    private OnBackPressedCallback onBackPressedCallback;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_business_profile);
        addToClickHandler(binding.header.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.header.backImgView.setRotation(180);
        }
        onBackPressedCallback = new OnBackPressedCallback(true) {
            @Override
            public void handleOnBackPressed() {
                if (businessProfileIntroFragment != null) {
                    businessProfileIntroFragment = null;
                    if (listdata.size() > 1) {
                        OpenSetUpScreen(listdata);
                    } else {
                        setEnabled(false);
                        getOnBackPressedDispatcher().onBackPressed();
                    }
                } else {
                    setEnabled(false);
                    getOnBackPressedDispatcher().onBackPressed();
                }
            }
        };

        getOnBackPressedDispatcher().addCallback(this, onBackPressedCallback);
    }

    public void generateErrorView() {
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (binding.errorView.getVisibility() != View.VISIBLE) {
            binding.errorView.setVisibility(View.VISIBLE);
        }
        binding.errorView.setOnRetryListener(this::displayProfileList);
    }

    @Override
    protected void onResume() {
        super.onResume();
        displayProfileList();
    }

    public Context getActContext() {
        return BusinessProfileActivity.this;
    }

    public void displayProfileList() {

        binding.mProgressBar.setVisibility(View.VISIBLE);
        binding.errorView.setVisibility(View.GONE);
        binding.mainContent.setVisibility(View.GONE);

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DisplayProfileList");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            listdata = new ArrayList<>();
            binding.mProgressBar.setVisibility(View.GONE);
            binding.mainContent.setVisibility(View.VISIBLE);

            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    JSONArray obj_arr = generalFunc.getJsonArray(Utils.message_str, responseObj);
                    HashMap<String, String> map;
                    if (obj_arr != null) {
                        if (obj_arr.length() == 1) {
                            map = new HashMap<>();
                            JSONObject intro_obj_temp = generalFunc.getJsonObject(obj_arr, 0);
                            map.put("vTitle", generalFunc.getJsonValueStr("vTitle", intro_obj_temp));
                            map.put("iUserProfileMasterId", generalFunc.getJsonValueStr("iUserProfileMasterId", intro_obj_temp));
                            map.put("vSubTitle", generalFunc.getJsonValueStr("vSubTitle", intro_obj_temp));
                            map.put("vScreenHeading", generalFunc.getJsonValueStr("vScreenHeading", intro_obj_temp));
                            map.put("vScreenTitle", generalFunc.getJsonValueStr("vScreenTitle", intro_obj_temp));
                            map.put("tDescription", generalFunc.getJsonValueStr("tDescription", intro_obj_temp));
                            map.put("vScreenButtonText", generalFunc.getJsonValueStr("vScreenButtonText", intro_obj_temp));
                            map.put("vImage", generalFunc.getJsonValueStr("vImage", intro_obj_temp));
                            map.put("vWelcomeImage", generalFunc.getJsonValueStr("vWelcomeImage", intro_obj_temp));
                            map.put("eStatus", generalFunc.getJsonValueStr("eStatus", intro_obj_temp));
                            map.put("vProfileName", generalFunc.getJsonValueStr("vProfileName", intro_obj_temp));
                            map.put("eProfileAdded", generalFunc.getJsonValueStr("eProfileAdded", intro_obj_temp));
                            map.put("email", generalFunc.getJsonValueStr("vProfileEmail", intro_obj_temp));
                            map.put("ProfileStatus", generalFunc.getJsonValueStr("ProfileStatus", intro_obj_temp));
                            map.put("vCompany", generalFunc.getJsonValueStr("vCompany", intro_obj_temp));
                            map.put("iOrganizationId", generalFunc.getJsonValueStr("iOrganizationId", intro_obj_temp));
                            map.put("iUserProfileId", generalFunc.getJsonValueStr("iUserProfileId", intro_obj_temp));

                            OpenProfileIntroScreen(map, false);

                        } else {
                            for (int i = 0; i < obj_arr.length(); i++) {
                                map = new HashMap<>();
                                JSONObject obj_temp = generalFunc.getJsonObject(obj_arr, i);
                                map.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
                                map.put("iUserProfileMasterId", generalFunc.getJsonValueStr("iUserProfileMasterId", obj_temp));
                                map.put("vSubTitle", generalFunc.getJsonValueStr("vSubTitle", obj_temp));
                                map.put("vScreenHeading", generalFunc.getJsonValueStr("vScreenHeading", obj_temp));
                                map.put("vScreenTitle", generalFunc.getJsonValueStr("vScreenTitle", obj_temp));
                                map.put("tDescription", generalFunc.getJsonValueStr("tDescription", obj_temp));
                                map.put("vScreenButtonText", generalFunc.getJsonValueStr("vScreenButtonText", obj_temp));
                                map.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));
                                map.put("vWelcomeImage", generalFunc.getJsonValueStr("vWelcomeImage", obj_temp));
                                map.put("eStatus", generalFunc.getJsonValueStr("eStatus", obj_temp));
                                map.put("vProfileName", generalFunc.getJsonValueStr("vProfileName", obj_temp));
                                map.put("eProfileAdded", generalFunc.getJsonValueStr("eProfileAdded", obj_temp));
                                map.put("email", generalFunc.getJsonValueStr("vProfileEmail", obj_temp));
                                map.put("ProfileStatus", generalFunc.getJsonValueStr("ProfileStatus", obj_temp));
                                map.put("vCompany", generalFunc.getJsonValueStr("vCompany", obj_temp));
                                map.put("iOrganizationId", generalFunc.getJsonValueStr("iOrganizationId", obj_temp));
                                map.put("iUserProfileId", generalFunc.getJsonValueStr("iUserProfileId", obj_temp));

                                listdata.add(map);
                            }
                            OpenSetUpScreen(listdata);
                        }
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), buttonId -> onBackPressed());
                    }

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), buttonId -> onBackPressed());
                }
            } else {
                generateErrorView();
            }
        });

    }

    public void setupTitleTxt(String title) {
        binding.header.titleTxt.setText(title);
    }

    public void OpenProfileIntroScreen(HashMap<String, String> map, boolean isMultipleProfileAdded) {
        if (map.get("eProfileAdded").equalsIgnoreCase("Yes")) {
            Bundle bn = new Bundle();
            bn.putSerializable("data", map);
            new ActUtils(getActContext()).startActWithData(MyBusinessProfileActivity.class, bn);
            if (!isMultipleProfileAdded) {
                finish();
            }
        } else {
            if (businessProfileIntroFragment != null) {
                businessProfileIntroFragment = null;
                Utils.runGC();
            }
            setupTitleTxt(map.get("vScreenHeading"));

            businessProfileIntroFragment = new BusinessProfileIntroFragment();
            Bundle bn = new Bundle();
            bn.putSerializable("Data", map);

            businessProfileIntroFragment.setArguments(bn);
            try {
                getSupportFragmentManager().beginTransaction()
                        .replace(R.id.mainContent, businessProfileIntroFragment).commit();
            } catch (Exception ignored) {
            }
        }
    }

    private void OpenSetUpScreen(ArrayList<HashMap<String, String>> map) {
        try {
            if (businessProfileListFragment != null) {
                businessProfileListFragment = null;
                Utils.runGC();
            }
            setupTitleTxt(generalFunc.retrieveLangLBl("", "LBL_PROFILE_SETUP"));
            businessProfileListFragment = new BusinessProfileListFragment();
            Bundle bn = new Bundle();
            bn.putSerializable("Data", map);

            businessProfileListFragment.setArguments(bn);
            getSupportFragmentManager().beginTransaction().replace(R.id.mainContent, businessProfileListFragment).commit();
        } catch (Exception ignored) {
        }
    }

    @Override
    public void onAlertButtonClick(int buttonId) {

    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.header.backImgView.getId()) {
            onBackPressedCallback.handleOnBackPressed();
        }
    }

}