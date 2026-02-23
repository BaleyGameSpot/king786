package com.act.rideSharingPro;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.act.rideSharingPro.adapter.RideShareRiderDetailsAdapter;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideSharePaymentSummaryBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class RideSharePaymentSummaryActivity extends ParentActivity {

    private ActivityRideSharePaymentSummaryBinding binding;
    private boolean isRideEnded = false, markPaymentCollect = false;
    private ImageView backImgView;
    private JSONObject riderDetails;
    private MButton paymentCollectedBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_share_payment_summary);

        initialization();
    }

    private void initialization() {
        backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAY_SUMMARY"));

        binding.loading.setVisibility(View.GONE);

        isRideEnded = getIntent().getBooleanExtra("isRideEnded", false);
        riderDetails = generalFunc.getJsonObject(getIntent().getStringExtra("riderDetails"));

        setData();
        updateVisibilityOfView();
    }

    private void setData() {
        if (riderDetails != null) {
            if (riderDetails.has("Payment_summery_user")) {
                JSONArray myArray = generalFunc.getJsonArray("Payment_summery_user", riderDetails);
                binding.riderDetailsRv.setAdapter(new RideShareRiderDetailsAdapter(generalFunc, myArray));
            }
        }
    }

    private void updateVisibilityOfView() {
        paymentCollectedBtn = ((MaterialRippleLayout) binding.paymentCollectedBtn).getChildView();
        paymentCollectedBtn.setId(Utils.generateViewId());
        addToClickHandler(paymentCollectedBtn);

        if (isRideEnded) {
            binding.paymentCollectedBtnArea.setVisibility(View.VISIBLE);
            backImgView.setVisibility(View.GONE);
            if (riderDetails != null) {
                paymentCollectedBtn.setText(generalFunc.getJsonValueStr("Label", riderDetails));
            }
        } else {
            binding.paymentCollectedBtnArea.setVisibility(View.GONE);
            backImgView.setVisibility(View.VISIBLE);
        }
    }

    private Context getActContext() {
        return RideSharePaymentSummaryActivity.this;
    }

    private void markPaymentCollect() {
        String publishedId;
        if (getIntent().hasExtra("publishRideId")) {
            publishedId = getIntent().getStringExtra("publishRideId");
        } else {
            generalFunc.showError(true);
            return;
        }

        binding.loading.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "publishRideUpdateState");
        parameters.put("iPublishedRideId", publishedId);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            binding.loading.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    riderDetails = generalFunc.getJsonObject(Utils.message_str, responseString);
                    markPaymentCollect = true;
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message), null,
                            generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), i -> sendResultBack());
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError();
            }
        });
    }


    private void sendResultBack() {
        Bundle bn = new Bundle();
        bn.putBoolean("isRideEnded", isRideEnded);
        bn.putBoolean("markPaymentCollect", markPaymentCollect);
        (new ActUtils(getActContext())).setOkResult(bn);
        finish();
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            sendResultBack();
        } else if (i == paymentCollectedBtn.getId()) {
            markPaymentCollect();
        }
    }
}