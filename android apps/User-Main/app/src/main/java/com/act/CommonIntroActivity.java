package com.act;

import android.content.Context;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.graphics.drawable.GradientDrawable;
import android.graphics.drawable.LayerDrawable;
import android.os.Bundle;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;

import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.viewpager.widget.ViewPager;

import com.ViewPagerCards.CardImagePagerAdapter;
import com.ViewPagerCards.ShadowTransformer;
import com.activity.ParentActivity;
import com.data.models.DataPreLoad;
import com.general.files.ActUtils;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityCommonIntroBinding;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class CommonIntroActivity extends ParentActivity {
    private ActivityCommonIntroBinding binding;
    private MButton bookNowBtn;
    private CardImagePagerAdapter mCardAdapter;
    private final ArrayList<HashMap<String, String>> imagesList = new ArrayList<>();
    private JSONObject dataObject;
    private String viewIntroType;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_common_intro);

        String dataObjStr = getIntent().getStringExtra("dataObject");
        try {
            if (Utils.checkText(dataObjStr)) {
                dataObject = new JSONObject(dataObjStr);
            }
        } catch (JSONException e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        initViews();

        viewIntroType = getIntent().getStringExtra("viewIntroType");
        if (Utils.checkText(viewIntroType)) {
            if (viewIntroType.equalsIgnoreCase("isTaxiBid")) {
                DataPreLoad.getInstance().retrieve(DataPreLoad.DataType.TAXI_BID_INFO, dataObj -> {
                    if (dataObj != null) {
                        if (!(dataObj instanceof JSONArray dataArr)) {
                            Logger.e("DataPreLoad", MyApp.getInstance().getCurrentAct() + " | dataObj >> " + dataObj.toString());
                            return;
                        }
                        imagesList(dataArr);
                    }
                });
            }
        } else {
            generalFunc.showError(true);
        }
    }

    private void initViews() {
        addToClickHandler(binding.backArrowImgView);
        bookNowBtn = ((MaterialRippleLayout) binding.bookNowBtn).getChildView();
        bookNowBtn.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW_TAXI_BID_TEXT"));
        bookNowBtn.setId(Utils.generateViewId());
        addToClickHandler(bookNowBtn);

        mCardAdapter = new CardImagePagerAdapter(getActContext());
        binding.bannerViewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {
                setBannerBGColor(imagesList.get(position).get("vBGColor"));
            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });
    }

    private void imagesList(@Nullable JSONArray imagesArray) {
        if (imagesArray != null && imagesArray.length() > 0) {
            if (generalFunc.isRTLmode()) {
                for (int x = imagesArray.length() - 1; x >= 0; x--) {
                    setImagesData(generalFunc.getJsonObject(imagesArray, x));
                }
            } else {
                for (int i = 0; i < imagesArray.length(); i++) {
                    setImagesData(generalFunc.getJsonObject(imagesArray, i));
                }
            }
        }

        binding.bannerViewPager.setAdapter(mCardAdapter);
        binding.bannerViewPager.setPageTransformer(false, new ShadowTransformer(binding.bannerViewPager, mCardAdapter));
        binding.bannerViewPager.setOffscreenPageLimit(3);
        if (imagesList.size() > 1) {
            binding.dotsIndicator.setVisibility(View.VISIBLE);
            binding.dotsIndicator.setViewPager(binding.bannerViewPager);
            setBannerBGColor(imagesList.get(0).get("vBGColor"));
        } else {
            binding.dotsIndicator.setVisibility(View.GONE);
        }
        if (generalFunc.isRTLmode()) {
            binding.bannerViewPager.setCurrentItem(imagesList.size() - 1);
        }
    }

    private void setImagesData(JSONObject imageObj) {
        HashMap<String, String> imagMap = new HashMap<>();
        imagMap.put("vImage", generalFunc.getJsonValueStr("vImage", imageObj));
        imagMap.put("tTitle", generalFunc.getJsonValueStr("tTitle", imageObj));
        imagMap.put("tSubtitle", generalFunc.getJsonValueStr("tSubtitle", imageObj));
        imagMap.put("vBGColor", generalFunc.getJsonValueStr("vBGColor", imageObj));
        imagesList.add(imagMap);
        mCardAdapter.addCardItem(imagMap, getActContext());
    }

    private void setBannerBGColor(String color) {
        try {
            Window window = getWindow();
            window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
            window.setStatusBarColor(Color.parseColor(color));
            setBannerBgDrawable(color);
        } catch (Exception e) {
            Logger.d("setBannerBGColor", "::" + e.getMessage());
        }
    }

    private void setBannerBgDrawable(String color) {
        Drawable topBannerDrawable = ContextCompat.getDrawable(getActContext(), R.drawable.banner_top_view_curve);
        LayerDrawable bubble = (LayerDrawable) topBannerDrawable;
        if (bubble != null) {
            GradientDrawable solidColor = (GradientDrawable) bubble.findDrawableByLayerId(R.id.bannerBgView);
            solidColor.setColor(Color.parseColor(color));
        }
    }

    private Context getActContext() {
        return CommonIntroActivity.this;
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.backArrowImgView.getId()) {
            onBackPressed();

        } else if (i == bookNowBtn.getId()) {
            if (dataObject != null) {
                generalFunc.storeData(viewIntroType, "Yes");
                Bundle bn = new Bundle();
                bn.putString("dataObject", dataObject.toString());
                (new ActUtils(this)).setOkResult(bn);
                finish();
            }
        }
    }
}