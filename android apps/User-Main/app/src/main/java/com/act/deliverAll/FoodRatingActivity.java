package com.act.deliverAll;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.animation.AnimationUtils;
import android.widget.FrameLayout;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.data.models.DataPreLoad;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.gson.Gson;
import com.act.HelpMainCategory23Pro;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityFoodRatingBinding;
import com.buddyverse.main.databinding.ItemDriverFeedbackQuestionsBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Objects;

public class FoodRatingActivity extends ParentActivity {

    private ActivityFoodRatingBinding binding;
    private MButton ratingBtnOld, ratingBtn;
    private int newDriverRating = 0, newRestaurantRating = 0;

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_food_rating);

        initViews();

        if (getIntent().getBooleanExtra("IS_NEW", false)) {
            driverFeedQuestionsInit();
        } else {
            restaurantDriverRatingInit();
        }
    }

    @SuppressLint("SetTextI18n")
    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RATING"));
        binding.orderNoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER") + " #" + getIntent().getStringExtra("vOrderNo"));

        if (getIntent().getBooleanExtra("IS_NEW", false)) {
            ratingBtn = ((MaterialRippleLayout) binding.ratingBtn).getChildView();
            ratingBtn.setId(Utils.generateViewId());
        } else {
            ratingBtnOld = ((MaterialRippleLayout) binding.ratingBtnOld).getChildView();
            ratingBtnOld.setId(Utils.generateViewId());
            addToClickHandler(ratingBtnOld);
        }

    }

    private void driverFeedQuestionsInit() {
        binding.driverFeedbackRatingArea.setVisibility(View.VISIBLE);
        binding.restaurantDriverRatingArea.setVisibility(View.GONE);

        binding.llBannerArea.setVisibility(View.VISIBLE);
        binding.txtRateBy.setText(generalFunc.retrieveLangLBl("", "LBL_RATE_DELIVERY_BY"));

        binding.providerNameTxt.setText(getIntent().getStringExtra("driverName"));

        ImageView iv_info = findViewById(R.id.editCartImageview);
        iv_info.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_information));
        iv_info.setVisibility(View.VISIBLE);
        iv_info.setOnClickListener(v -> {
            Bundle bn = new Bundle();
            bn.putString("iOrderId", getIntent().getStringExtra("iOrderId"));
            new ActUtils(getActContext()).startActWithData(HelpMainCategory23Pro.class, bn);
        });

        binding.disableArea.setVisibility(View.VISIBLE);
        binding.llratingArea.setVisibility(View.VISIBLE);
        binding.llDriverNameRating1.setVisibility(View.VISIBLE);
        binding.llDriverNameRating2.setVisibility(View.GONE);
        binding.llStoreRating.setVisibility(View.GONE);

        binding.txtTellUs.mTextH.setVisibility(View.GONE);
        binding.txtTellUs.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_FEEDBACK"));
        FrameLayout.LayoutParams mParams = (FrameLayout.LayoutParams) binding.txtTellUs.mEditText.getLayoutParams();
        mParams.height = getResources().getDimensionPixelSize(R.dimen._70sdp);
        binding.txtTellUs.mEditText.setLayoutParams(mParams);
        MyUtils.editBoxMultiLine(binding.txtTellUs.mEditText);

        binding.thanksNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_WORDS"));

        ratingBtn.setVisibility(View.GONE);
        ratingBtn.setText(generalFunc.retrieveLangLBl("", "LBL_SUBMIT_FEEDBACK"));

        // Star Rating Emoji images
        binding.llStarEmojiArea.setVisibility(View.VISIBLE);

        starRatingEmojiHide(binding.ratingBarDriver.getRating(), binding.ivStarEmoji1, binding.ivStarEmoji2, binding.ivStarEmoji3, binding.ivStarEmoji4, binding.ivStarEmoji5);

        if (getIntent().getStringExtra("eTakeaway") != null && getIntent().getStringExtra("eTakeaway").equalsIgnoreCase("Yes")) {
            if (binding.llBannerArea.getVisibility() == View.VISIBLE) {
                binding.llBannerArea.setVisibility(View.GONE);
                ratingBtn.setVisibility(View.VISIBLE);
                binding.llStoreRating.setVisibility(View.VISIBLE);

                binding.llDriverNameRating1.setVisibility(View.GONE);
                binding.llDriverNameRating2.setVisibility(View.GONE);
                binding.llratingArea.setVisibility(View.GONE);
            }
            if (binding.ratingBarRestaurant.getRating() > 0) {
                binding.disableArea.setVisibility(View.GONE);
            } else {
                binding.disableArea.setVisibility(View.VISIBLE);
            }
        }
        binding.ratingBarDriver.setOnRatingBarChangeListener((simpleRatingBar, v, b) -> {
            float rating = simpleRatingBar.getRating();
            if (rating <= 1) {
                newDriverRating = 1;
            } else if (rating <= 2) {
                newDriverRating = 2;
            } else if (rating <= 3) {
                newDriverRating = 3;
            } else if (rating <= 4) {
                newDriverRating = 4;
            } else if (rating <= 5) {
                newDriverRating = 5;
            }
            if (rating > 0) {
                if (binding.llBannerArea.getVisibility() == View.VISIBLE) {
                    binding.llBannerArea.setVisibility(View.GONE);
                    ratingBtn.setVisibility(View.VISIBLE);
                    binding.llratingArea.setVisibility(View.VISIBLE);
                    binding.llDriverNameRating2.setVisibility(View.VISIBLE);
                    binding.llStoreRating.setVisibility(View.VISIBLE);
                    binding.llQuestionArea.startAnimation(AnimationUtils.loadAnimation(getActContext(), R.anim.slide_up_anim));
                }
                if (binding.ratingBarRestaurant.getRating() > 0) {
                    binding.disableArea.setVisibility(View.GONE);
                } else {
                    binding.disableArea.setVisibility(View.VISIBLE);
                }
            } else {
                binding.disableArea.setVisibility(View.VISIBLE);
            }
            starRatingEmojiHide(simpleRatingBar.getRating(), binding.ivStarEmoji1, binding.ivStarEmoji2, binding.ivStarEmoji3, binding.ivStarEmoji4, binding.ivStarEmoji5);
        });

        // list data Container
        List<HashMap<String, String>> arrayList = new ArrayList<>();
        DataPreLoad.getInstance().retrieve(DataPreLoad.DataType.FOOD_RATING_DRIVER_FEEDBACK_QUESTIONS, dataObj -> {
            if (!(dataObj instanceof JSONArray dataArr)) {
                Logger.e("DataPreLoad", MyApp.getInstance().getCurrentAct() + " | dataObj >> " + dataObj.toString());
                return;
            }

            for (int i = 0; i < dataArr.length(); i++) {

                JSONObject data = generalFunc.getJsonObject(dataArr, i);

                LayoutInflater layoutInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                @NonNull ItemDriverFeedbackQuestionsBinding iBinding = ItemDriverFeedbackQuestionsBinding.inflate(layoutInflater, binding.dataContainer, false);
                iBinding.txtQuestion.setText(generalFunc.getJsonValueStr("tQuestion", data));

                HashMap<String, String> hashMap = new HashMap<>();
                hashMap.put("iFeedbackId", generalFunc.getJsonValueStr("iFeedbackId", data));
                hashMap.put("ans", "");
                arrayList.add(hashMap);

                iBinding.btnYes.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
                iBinding.btnNo.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
                iBinding.btnYes.setOnClickListener(v -> {
                    iBinding.btnYes.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.btn_border));
                    iBinding.btnNo.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.border_black_line));

                    iBinding.btnYes.setTextColor(getActContext().getResources().getColor(R.color.white));
                    iBinding.btnNo.setTextColor(getActContext().getResources().getColor(R.color.black));
                    hashMap.put("ans", "Yes");
                });
                iBinding.btnNo.setOnClickListener(v -> {
                    iBinding.btnYes.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.border_black_line));
                    iBinding.btnNo.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.btn_border));

                    iBinding.btnYes.setTextColor(getActContext().getResources().getColor(R.color.black));
                    iBinding.btnNo.setTextColor(getActContext().getResources().getColor(R.color.white));
                    hashMap.put("ans", "No");
                });

                binding.dataContainer.addView(iBinding.getRoot());
            }
        });

        // Restaurant Rating
        binding.restaurantNameTxt.setText(getIntent().getStringExtra("vCompany"));

        // Restaurant Star Rating Emoji images
        binding.llStarEmojiRestaurantArea.setVisibility(View.VISIBLE);

        starRatingEmojiHide(binding.ratingBarRestaurant.getRating(), binding.ivStarEmojiR1, binding.ivStarEmojiR2, binding.ivStarEmojiR3, binding.ivStarEmojiR4, binding.ivStarEmojiR5);
        binding.ratingBarRestaurant.setOnRatingBarChangeListener((simpleRatingBar, v, b) -> {
            float rating = simpleRatingBar.getRating();
            if (rating == 0) {
                newRestaurantRating = 0;
            } else if (rating <= 1) {
                newRestaurantRating = 1;
            } else if (rating <= 2) {
                newRestaurantRating = 2;
            } else if (rating <= 3) {
                newRestaurantRating = 3;
            } else if (rating <= 4) {
                newRestaurantRating = 4;
            } else if (rating <= 5) {
                newRestaurantRating = 5;
            }
            starRatingEmojiHide(simpleRatingBar.getRating(), binding.ivStarEmojiR1, binding.ivStarEmojiR2, binding.ivStarEmojiR3, binding.ivStarEmojiR4, binding.ivStarEmojiR5);
            if (getIntent().getStringExtra("eTakeaway") != null && getIntent().getStringExtra("eTakeaway").equalsIgnoreCase("Yes")) {
                if (binding.ratingBarRestaurant.getRating() > 0) {
                    binding.disableArea.setVisibility(View.GONE);
                } else {
                    binding.disableArea.setVisibility(View.VISIBLE);
                }
            } else {
                if (binding.ratingBarDriver.getRating() > 0 && binding.ratingBarRestaurant.getRating() > 0) {
                    binding.disableArea.setVisibility(View.GONE);
                } else {
                    binding.disableArea.setVisibility(View.VISIBLE);
                }
            }
        });
        binding.txtTellUsRestaurant.mTextH.setVisibility(View.GONE);
        binding.txtTellUsRestaurant.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_TELL_US"));
        FrameLayout.LayoutParams mParams1 = (FrameLayout.LayoutParams) binding.txtTellUsRestaurant.mEditText.getLayoutParams();
        mParams1.height = getResources().getDimensionPixelSize(R.dimen._70sdp);
        binding.txtTellUsRestaurant.mEditText.setLayoutParams(mParams1);
        MyUtils.editBoxMultiLine(binding.txtTellUsRestaurant.mEditText);

        ratingBtn.setOnClickListener(v -> {
            //
            driverFeedQuestionsRating(binding.txtTellUsRestaurant.mEditText, new Gson().toJson(arrayList), binding.txtTellUsRestaurant.mEditText);
        });

    }

    private void starRatingEmojiHide(float rating, ImageView ivStarEmoji_1, ImageView ivStarEmoji_2, ImageView ivStarEmoji_3, ImageView ivStarEmoji_4, ImageView ivStarEmoji_5) {
        ivStarEmoji_1.setVisibility(View.INVISIBLE);
        ivStarEmoji_2.setVisibility(View.INVISIBLE);
        ivStarEmoji_3.setVisibility(View.INVISIBLE);
        ivStarEmoji_4.setVisibility(View.INVISIBLE);
        ivStarEmoji_5.setVisibility(View.INVISIBLE);

        if (rating == 0) {
            ivStarEmoji_1.setVisibility(View.INVISIBLE);
        } else if (rating <= 1) {
            ivStarEmoji_1.setVisibility(View.VISIBLE);
        } else if (rating <= 2) {
            ivStarEmoji_2.setVisibility(View.VISIBLE);
        } else if (rating <= 3) {
            ivStarEmoji_3.setVisibility(View.VISIBLE);
        } else if (rating <= 4) {
            ivStarEmoji_4.setVisibility(View.VISIBLE);
        } else if (rating <= 5) {
            ivStarEmoji_5.setVisibility(View.VISIBLE);
        }
    }

    private void driverFeedQuestionsRating(MaterialEditText txtTellUs, String jsonArray, MaterialEditText txtTellUsRestaurant) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "submitRating");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iOrderId", getIntent().getStringExtra("iOrderId"));

        parameters.put("ENABLE_FOOD_RATING_DETAIL_FLOW", "Yes");
        parameters.put("isDetailRatingForDriver", "Yes");
        if (getIntent().getStringExtra("eTakeaway") == null || !getIntent().getStringExtra("eTakeaway").equalsIgnoreCase("Yes")) {
            parameters.put("driverFeedbackDetails", jsonArray);
        }

        parameters.put("rating", "" + newRestaurantRating);
        parameters.put("message", Objects.requireNonNull(txtTellUsRestaurant.getText()).toString());
        parameters.put("tripID", "");
        parameters.put("rating1", "" + newDriverRating);
        parameters.put("message1", Objects.requireNonNull(txtTellUs.getText()).toString());

        parameters.put("eFromUserType", Utils.userType);
        parameters.put("eToUserType", "Company");
        parameters.put("eSystem", Utils.eSystem_Type);
        parameters.put("Platform", Utils.deviceType);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    MyApp.getInstance().storeUserData(responseString);

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> finish());
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        });

    }

    @SuppressLint("SetTextI18n")
    private void restaurantDriverRatingInit() {
        binding.restaurantDriverRatingArea.setVisibility(View.VISIBLE);
        binding.driverFeedbackRatingArea.setVisibility(View.GONE);

        binding.ratingResNameTxt.setText(getIntent().getStringExtra("vCompany"));
        binding.ratingDriverNameTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RATE_DELIVERY_BY") + " " + getIntent().getStringExtra("driverName"));

        binding.disableArea1.setVisibility(View.VISIBLE);

        binding.resCommentBox.mTextH.setVisibility(View.GONE);
        binding.resCommentBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_RESTAURANT_RATING_NOTE"));
        FrameLayout.LayoutParams mParams = (FrameLayout.LayoutParams) binding.resCommentBox.mEditText.getLayoutParams();
        mParams.height = getResources().getDimensionPixelSize(R.dimen._70sdp);
        binding.resCommentBox.mEditText.setLayoutParams(mParams);
        MyUtils.editBoxMultiLine(binding.resCommentBox.mEditText);

        binding.driverCommentBox.mTextH.setVisibility(View.GONE);
        binding.driverCommentBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_DRIVER_RATING_NOTE"));
        FrameLayout.LayoutParams mParams1 = (FrameLayout.LayoutParams) binding.driverCommentBox.mEditText.getLayoutParams();
        mParams1.height = getResources().getDimensionPixelSize(R.dimen._70sdp);
        binding.driverCommentBox.mEditText.setLayoutParams(mParams1);
        MyUtils.editBoxMultiLine(binding.driverCommentBox.mEditText);

        ratingBtnOld.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_DELIVERY_RATING"));

        binding.ratingBarRes.setOnRatingBarChangeListener((simpleRatingBar, v, b) -> {
            if (getIntent().getStringExtra("eTakeaway") != null && getIntent().getStringExtra("eTakeaway").equalsIgnoreCase("Yes")) {
                if (binding.ratingBarRes.getRating() > 0) {
                    binding.disableArea1.setVisibility(View.GONE);
                } else {
                    binding.disableArea1.setVisibility(View.VISIBLE);
                }
            } else {
                if (binding.ratingBarRes.getRating() > 0 && binding.ratingBarDriverOld.getRating() > 0) {
                    binding.disableArea1.setVisibility(View.GONE);
                } else {
                    binding.disableArea1.setVisibility(View.VISIBLE);
                }
            }
        });
        binding.ratingBarRes.setRating(getIntent().getFloatExtra("rating", 0));

        binding.ratingBarDriverOld.setOnRatingBarChangeListener((simpleRatingBar, v, b) -> {
            if (binding.ratingBarRes.getRating() > 0 && binding.ratingBarDriverOld.getRating() > 0) {
                binding.disableArea1.setVisibility(View.GONE);
            } else {
                binding.disableArea1.setVisibility(View.VISIBLE);
            }
        });

        if (getIntent().getStringExtra("eTakeaway") != null && getIntent().getStringExtra("eTakeaway").equalsIgnoreCase("Yes")) {
            binding.driverAreaRating.setVisibility(View.GONE);
        }
    }


    private void ratingFood() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "submitRating");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iOrderId", getIntent().getStringExtra("iOrderId"));

        parameters.put("rating", binding.ratingBarRes.getRating() + "");
        parameters.put("message", Objects.requireNonNull(binding.resCommentBox.mEditText.getText()).toString());
        parameters.put("rating1", binding.ratingBarDriverOld.getRating() + "");
        parameters.put("message1", Objects.requireNonNull(binding.driverCommentBox.mEditText.getText()).toString());

        parameters.put("eFromUserType", Utils.userType);
        parameters.put("eToUserType", "Company");
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    MyApp.getInstance().storeUserData(responseString);

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> finish());
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        });
    }

    private Activity getActContext() {
        return FoodRatingActivity.this;
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            FoodRatingActivity.super.onBackPressed();
        } else if (i == ratingBtnOld.getId() || i == ratingBtn.getId()) {
            ratingFood();
        }
    }
}