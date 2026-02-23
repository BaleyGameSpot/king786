package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.DonationBannerAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityDonationBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;

import java.util.HashMap;

public class DonationActivity extends ParentActivity {

    private ActivityDonationBinding binding;
    private DonationBannerAdapter donationBannerAdapter;
    private JSONArray donationListArray = new JSONArray();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_donation);

        initialization();
        getDonationDetails();
    }

    private void initialization() {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DONATE"));

        donationBannerAdapter = new DonationBannerAdapter(getActContext(), generalFunc, donationListArray, (position, mItemObj) -> {
            Logger.d("URL", "::" + generalFunc.getJsonValueStr("tLink", mItemObj));
            new ActUtils(getActContext()).openURL(generalFunc.getJsonValueStr("tLink", mItemObj));
        });
        binding.donateListRV.setAdapter(donationBannerAdapter);
    }

    private Context getActContext() {
        return DonationActivity.this;
    }

    private void getDonationDetails() {
        binding.loading.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDonation");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            binding.loading.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    donationListArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    donationBannerAdapter.updateData(donationListArray);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), buttonId -> binding.toolbarInclude.backImgView.performClick());
                }
            } else {
                generalFunc.showError(true);
            }
        });
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == binding.toolbarInclude.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        }
    }
}