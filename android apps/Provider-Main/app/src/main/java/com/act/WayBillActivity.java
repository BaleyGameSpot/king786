package com.act;

import android.content.Context;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityWayBillBinding;
import com.buddyverse.providers.databinding.ItemWaybillDataBinding;
import com.buddyverse.providers.databinding.ItemWaybillHeaderBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class WayBillActivity extends ParentActivity {

    private ActivityWayBillBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_way_bill);

        initViews();
        getWayBillDetails();
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);

        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MENU_WAY_BILL"));
    }

    private void getWayBillDetails() {
        binding.errorView.setVisibility(View.GONE);
        binding.loading.setVisibility(View.VISIBLE);
        binding.scrollView.setVisibility(View.GONE);

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "displayWayBill");
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(this, parameters, responseString -> {
            binding.loading.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {

                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    binding.scrollView.setVisibility(View.VISIBLE);

                    dataSet(message);

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("No Record Found", message), buttonId -> finish());
                }

            } else {
                generateErrorView();
            }

        });
    }

    private void dataSet(String message) {
        JSONArray mainDataArr = generalFunc.getJsonArray(message);
        if (binding.dataArea.getChildCount() > 0) {
            binding.dataArea.removeAllViewsInLayout();
        }
        if (mainDataArr != null) {
            for (int i = 0; i < mainDataArr.length(); i++) {
                JSONObject mainObj = generalFunc.getJsonObject(mainDataArr, i);

                final LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                @NonNull ItemWaybillHeaderBinding bindingH = ItemWaybillHeaderBinding.inflate(inflater, binding.dataArea, false);
                bindingH.titleHTxt.setText(generalFunc.getJsonValueStr("vTitle", mainObj));

                JSONArray detailsArr = generalFunc.getJsonArray("Details", mainObj);
                if (detailsArr != null) {
                    for (int j = 0; j < detailsArr.length(); j++) {
                        JSONObject dataObj = generalFunc.getJsonObject(detailsArr, j);

                        @NonNull ItemWaybillDataBinding bindingData = ItemWaybillDataBinding.inflate(inflater, bindingH.serviceSelectArea, false);
                        bindingData.titleTxt.setText(generalFunc.getJsonValueStr("vTitle", dataObj));

                        String vValue = generalFunc.getJsonValueStr("vValue", dataObj);
                        if (Utils.checkText(vValue)) {
                            bindingData.valueTxt.setText(vValue);
                        } else {
                            bindingData.valueTxt.setVisibility(View.GONE);
                            bindingData.titleTxt.setGravity(Gravity.CENTER);

                            bindingData.titleTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                            bindingData.titleTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 17);

                            if (j == 0) {
                                bindingData.itemArea.setBackground(ContextCompat.getDrawable(this, R.drawable.top_curve_card_genie));
                                bindingData.itemArea.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor("#E5E5E5")));
                            } else {
                                bindingData.itemArea.setBackgroundColor(Color.parseColor("#E5E5E5"));
                            }
                        }

                        bindingData.lineVew.setVisibility(j == (detailsArr.length() - 1) ? View.GONE : View.VISIBLE);

                        bindingH.serviceSelectArea.addView(bindingData.getRoot());
                    }
                }
                binding.dataArea.addView(bindingH.getRoot());
            }
        }
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setVisibility(View.VISIBLE);
        binding.errorView.setOnRetryListener(this::getWayBillDetails);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();

        }
    }
}