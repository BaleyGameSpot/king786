package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityHelpMainCategory23ProBinding;
import com.buddyverse.main.databinding.ItemDesignCategoryHelpBinding;
import com.buddyverse.main.databinding.ItemDesignCategoryHelpTitleBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class HelpMainCategory23Pro extends ParentActivity {

    private ActivityHelpMainCategory23ProBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_help_main_category23_pro);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_HELP_TXT"));
        getHelpList();
    }

    private Context getActContext() {
        return HelpMainCategory23Pro.this;
    }

    private void getHelpList() {
        binding.loadingBar.setVisibility(View.VISIBLE);
        binding.contentView.setVisibility(View.GONE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getHelpDetailCategoty");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("appType", Utils.app_type);

        if (getIntent().hasExtra("iOrderId")) {
            parameters.put("iOrderId", getIntent().getStringExtra("iOrderId"));
            parameters.put("eSystem", Utils.eSystem_Type);
        } else if (getIntent().hasExtra("iBiddingPostId")) {
            parameters.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));

        } else {
            parameters.put("iTripId", getIntent().getStringExtra("iTripId"));
        }

        binding.noHelpTxt.setVisibility(View.GONE);
        ApiHandler.execute(getActContext(), parameters, responseString -> {
            binding.noHelpTxt.setVisibility(View.GONE);
            binding.loadingBar.setVisibility(View.GONE);
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);
            if (responseStringObject != null && !responseStringObject.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    JSONArray carList_arr = generalFunc.getJsonArray(Utils.message_str, responseStringObject);
                    if (carList_arr != null) {
                        binding.contentView.setVisibility(View.VISIBLE);
                        if (binding.serviceSelectHArea.getChildCount() > 0) {
                            binding.serviceSelectHArea.removeAllViewsInLayout();
                        }
                        for (int i = 0; i < carList_arr.length(); i++) {
                            buildHelpTitle(generalFunc.getJsonObject(carList_arr, i));
                        }
                    }
                } else {
                    binding.noHelpTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    binding.noHelpTxt.setVisibility(View.VISIBLE);
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @SuppressLint({"SetTextI18n", "InflateParams"})
    private void buildHelpTitle(JSONObject obj) {
        if (obj == null) {
            return;
        }
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull ItemDesignCategoryHelpTitleBinding binding1 = ItemDesignCategoryHelpTitleBinding.inflate(inflater, binding.serviceSelectHArea, false);
        binding1.titleTxtHelp.setText(generalFunc.getJsonValueStr("vTitle", obj));

        binding1.shadowHeaderView.setVisibility(View.GONE);
        JSONArray mCategory = generalFunc.getJsonArray("subData", obj);
        if (mCategory == null || mCategory.length() == 0) {
            return;
        }
        for (int j = 0; j < mCategory.length(); j++) {
            HelpServices(generalFunc.getJsonObject(mCategory, j), binding1.helpDetailsArea, j == (mCategory.length() - 1), obj);
        }
        binding.serviceSelectHArea.addView(binding1.getRoot());
    }

    @SuppressLint({"SetTextI18n", "InflateParams"})
    private void HelpServices(JSONObject jsonObject, LinearLayout serviceSelectArea, boolean isLast, JSONObject obj) {
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull ItemDesignCategoryHelpBinding binding2 = ItemDesignCategoryHelpBinding.inflate(inflater, serviceSelectArea, false);

        if (generalFunc.isRTLmode()) {
            binding2.imageArrow.setRotation(90);
        } else {
            binding2.imageArrow.setRotation(-90);
        }

        binding2.detailsTxt.setText(generalFunc.getJsonValueStr("vTitle", jsonObject));
        binding2.layoutBackground.setOnClickListener(view -> {
            Bundle bn = new Bundle();
            bn.putString("iHelpDetailId", generalFunc.getJsonValueStr("iHelpDetailId", jsonObject));
            bn.putString("vTitle", generalFunc.getJsonValueStr("vSubTitle", jsonObject));
            bn.putString("tAnswer", generalFunc.getJsonValueStr("tAnswer", jsonObject));
            bn.putString("eShowFrom", generalFunc.getJsonValueStr("eShowFrom", jsonObject));
            bn.putString("iUniqueId", generalFunc.getJsonValueStr("iUniqueId", obj));
            if (getIntent().hasExtra("iOrderId")) {
                bn.putString("iOrderId", getIntent().getStringExtra("iOrderId"));
            } else if (getIntent().hasExtra("iBiddingPostId")) {
                bn.putString("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));
            } else {
                bn.putString("iTripId", getIntent().getStringExtra("iTripId"));
            }
            new ActUtils(getActContext()).startActWithData(Help_DetailsActivity.class, bn);
        });
        serviceSelectArea.addView(binding2.getRoot());
        if (isLast) {
            binding2.seperationLine.setVisibility(View.GONE);
        }
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        }
    }
}