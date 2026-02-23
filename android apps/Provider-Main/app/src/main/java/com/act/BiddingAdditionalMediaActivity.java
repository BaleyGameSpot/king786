package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.BidAdditionalMediaAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityBiddingMediaBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONObject;

import java.util.HashMap;

public class BiddingAdditionalMediaActivity extends ParentActivity {

    private ActivityBiddingMediaBinding binding;
    private BidAdditionalMediaAdapter mediaAdapter;
    private String iBiddingPostId;

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_bidding_media);

        iBiddingPostId = getIntent().getStringExtra("iBiddingPostId");

        initViews();
        if (Utils.checkText(iBiddingPostId)) {
            binding.descriptionArea.setVisibility(View.GONE);
            getMedia();
        } else {
            getDriverMedia();
        }
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_MEDIA_TXT"));

        mediaAdapter = new BidAdditionalMediaAdapter(getActContext(), generalFunc, null, 2, false, new BidAdditionalMediaAdapter.OnItemClickListener() {
            @Override
            public void onItemClickList(int position, JSONObject itemObject) {
                new ActUtils(getActContext()).openURL(generalFunc.getJsonValueStr("vImage", itemObject));
            }

            @Override
            public void onDeleteClick(int position, JSONObject itemObject) {

            }
        });
        binding.bidAdditionalMediaRV.setAdapter(mediaAdapter);
        binding.noDataTxt.setVisibility(View.GONE);
    }

    private Context getActContext() {
        return BiddingAdditionalMediaActivity.this;
    }

    private void getMedia() {
        binding.mProgressBar.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UploadBiddingMedia");
        parameters.put("action_type", "GET");
        parameters.put("iBiddingPostId", iBiddingPostId);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            binding.mProgressBar.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                mediaAdapter.updateData(generalFunc.getJsonArray("BiddingPostMedia", responseString));
            } else {
                generalFunc.showError();
            }
        });
    }

    private void getDriverMedia() {
        binding.mProgressBar.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getProviderMedia");
        parameters.put("DriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            binding.mProgressBar.setVisibility(View.GONE);
            binding.noDataTxt.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    mediaAdapter.updateData(generalFunc.getJsonArray("MEDIA", message));
                    String desc = generalFunc.getJsonValue("DESCRIPTION", message);

                    if (Utils.checkText(desc)) {
                        binding.descriptionHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DESCRIPTION"));
                        binding.descriptionVTxt.setText(desc);
                    } else {
                        binding.descriptionArea.setVisibility(View.GONE);
                    }
                } else {
                    binding.noDataTxt.setVisibility(View.VISIBLE);
                    binding.noDataTxt.setText(generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public void onClick(View view) {
        if (view.getId() == R.id.backImgView) {
            finish();
        }
    }
}