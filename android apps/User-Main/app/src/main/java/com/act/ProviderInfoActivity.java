package com.act;

import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityProviderInfoBinding;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;

public class ProviderInfoActivity extends ParentActivity {

    private ActivityProviderInfoBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_provider_info);

        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        //binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_DESCRIPTION"));

        String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + getIntent().getStringExtra("iDriverId") + "/" + getIntent().getStringExtra("driver_img");
        new LoadImage.builder(LoadImage.bind(image_url), binding.providerImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
        binding.nameTxt.setText(getIntent().getStringExtra("name"));
        binding.ratingBar.setRating(GeneralFunctions.parseFloatValue(0, getIntent().getStringExtra("average_rating")));

        //binding.descTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT") + " " + getIntent().getStringExtra("fname"));
        binding.descTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_EXPERT"));

        String tProfileDescription = getIntent().getStringExtra("tProfileDescription");
        if (Utils.checkText(tProfileDescription)) {
            binding.descTxt.setVisibility(View.VISIBLE);
            binding.descTxt.setText(tProfileDescription);
        } else {
            binding.descTxt.setVisibility(View.GONE);
        }
    }

    public void onClick(View view) {
        Utils.hideKeyboard(this);
        if (view.getId() == binding.toolbarInclude.backImgView.getId()) {
            finish();
        }
    }
}