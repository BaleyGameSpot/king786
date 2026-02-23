package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.os.Bundle;
import android.text.SpannableString;
import android.text.SpannableStringBuilder;
import android.text.style.ForegroundColorSpan;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.github.mikephil.charting.charts.PieChart;
import com.github.mikephil.charting.data.PieData;
import com.github.mikephil.charting.data.PieDataSet;
import com.github.mikephil.charting.data.PieEntry;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class DriverRewardActivity extends ParentActivity {

    private JSONObject rewardObj;
    private LinearLayout llChartContainerView;
    MTextView progressTitle;
    MTextView txtStartDate;
    MTextView txtEndDate;
    MTextView rewardAmount;
    MTextView rewardTitle;
    ImageView backImgView;
    MTextView txtRewardTitle;
    ImageView ivRewordImg;
    MTextView btnAchieved, btnNextLevel, levelStatus, rewardNote;
    MTextView txtNote;
    String levelTag = "Next";
    LinearLayout llLevels;
    boolean isLoading = false;
    String responseStr = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_reward);
        progressTitle = findViewById(R.id.progressTitle);
        progressTitle.setText(generalFunc.retrieveLangLBl("Your Progress", "LBL_YOUR_PROGRESS"));
        backImgView = findViewById(R.id.backImgView);
        backImgView.setOnClickListener(v -> finish());
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REWARD_PROGRAM"));


        txtStartDate = findViewById(R.id.txtStartDate);
        txtEndDate = findViewById(R.id.txtEndDate);
        rewardAmount = findViewById(R.id.rewardAmount);
        rewardTitle = findViewById(R.id.rewardTitle);
        txtRewardTitle = findViewById(R.id.txtRewardTitle);
        ivRewordImg = findViewById(R.id.ivRewardImg);
        btnAchieved = findViewById(R.id.txtAchieved);
        btnNextLevel = findViewById(R.id.txtNextLevel);
        llLevels = findViewById(R.id.llLevels);
        levelStatus = findViewById(R.id.levelStatus);
        rewardNote = findViewById(R.id.rewardNote);
        txtNote = findViewById(R.id.txtNote);
        addToClickHandler(btnAchieved);
        addToClickHandler(btnNextLevel);
        btnNextLevel.setBackgroundTintList(ColorStateList.valueOf(getResources().getColor(R.color.appThemeColor_1)));
        btnAchieved.setBackgroundTintList(ColorStateList.valueOf(getResources().getColor(R.color.white)));
        btnNextLevel.setTextColor(getResources().getColor(R.color.white));
        btnAchieved.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
        btnAchieved.setText(generalFunc.retrieveLangLBl("", "LBL_DRIVER_REWARD_ACHIEVED_TAG_TEXT"));
        btnNextLevel.setText(generalFunc.retrieveLangLBl("", "LBL_DRIVER_REWARD_NEXT_LEVEL_TAG_TEXT"));
        llLevels.setVisibility(View.GONE);


        getData();
    }

    private void getData() {
        RelativeLayout ll_progressBar = findViewById(R.id.loading);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverRewardInfo");
        parameters.put("LevelTag", levelTag);
        parameters.put("GeneralMemberId", generalFunc.getMemberId());
        ll_progressBar.setVisibility(View.VISIBLE);
        isLoading = true;

        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {
            isLoading = false;
            ll_progressBar.setVisibility(View.GONE);
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    responseStr = responseString;

                    rewardObj = generalFunc.getJsonObject("message", responseString);
                    initViews();

                    charView();

                    dataListView();

                    howItWork();

                    rewardCompleted();

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });


    }

    private void rewardCompleted() {
        if (generalFunc.getJsonValueStr("all_reward_completed", rewardObj).equalsIgnoreCase("Yes")) {
            LinearLayout topView = findViewById(R.id.topView);
            topView.setVisibility(View.INVISIBLE);
            if (llChartContainerView.getChildCount() > 0) {
                llChartContainerView.removeAllViewsInLayout();
            }
            LinearLayout.LayoutParams lparams = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            MTextView tv = new MTextView(this);
            tv.setLayoutParams(lparams);
            tv.setTextSize(25f);
            tv.setText(generalFunc.getJsonValueStr("reward_completed_text", rewardObj));
            llChartContainerView.addView(tv);
        }
    }

    @SuppressLint("SetTextI18n")
    private void initViews() {
        if (levelTag.equalsIgnoreCase("Next")) {
            levelStatus.setText("(" + generalFunc.retrieveLangLBl("", "LBL_REWARD_IN_PROGRESS_TEXT") + ")");
        } else if (levelTag.equalsIgnoreCase("Achieved")) {
            levelStatus.setText("(" + generalFunc.retrieveLangLBl("", "LBL_DRIVER_REWARD_ACHIEVED_TAG_TEXT") + ")");
        }

        if (generalFunc.getJsonValueStr("all_reward_completed", rewardObj).equalsIgnoreCase("yes")) {
            levelStatus.setText("(" + generalFunc.retrieveLangLBl("", "LBL_DRIVER_REWARD_ACHIEVED_TAG_TEXT") + ")");
        }
        rewardNote.setText(generalFunc.getJsonValueStr("REWARD_AMOUNT_TEXT", rewardObj));
        txtNote.setText(generalFunc.retrieveLangLBl("", "LBL_REWARD_NOTE_TEXT"));
//        rewardObj = generalFunc.getJsonObject(generalFunc.getJsonValue("reward", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        txtRewardTitle.setText(generalFunc.getJsonValueStr("vTitle", rewardObj));
        txtEndDate.setText(generalFunc.getJsonValueStr("END_DATE", rewardObj));
        if (!Utils.checkText(generalFunc.getJsonValueStr("END_DATE", rewardObj))) {
            txtStartDate.setGravity(Gravity.CENTER);
        } else {
            txtStartDate.setGravity(Gravity.START);
        }
        txtStartDate.setText(generalFunc.getJsonValueStr("DISPLAY_START_DATE", rewardObj));
        rewardAmount.setText(generalFunc.getJsonValueStr("REWARD_AMOUNT", rewardObj));
        rewardTitle.setText(generalFunc.retrieveLangLBl("", "LBL_REWARD_AMOUNT_TEXT"));
        txtRewardTitle.setTextSize(getActContext().getResources().getDimension(R.dimen._10sdp));
        String img = generalFunc.getJsonValueStr("vImage", rewardObj);
        if (generalFunc.getJsonValueStr("ENABLE_ACHIEVED_BTN", rewardObj).equalsIgnoreCase("yes") && generalFunc.getJsonValueStr("ENABLE_NEXT_LEVEL", rewardObj).equalsIgnoreCase("yes")) {
            llLevels.setVisibility(View.VISIBLE);
        } else {
            llLevels.setVisibility(View.GONE);
        }
        if (Utils.checkText(img)) {
            new LoadImage.builder(LoadImage.bind(img), ivRewordImg).build();
        } else {
            ivRewordImg.setVisibility(View.GONE);
        }


        ImageView ivArrowView = findViewById(R.id.ivArrowView);
        ivArrowView.setOnClickListener(v -> new ActUtils(getActContext()).startAct(DriverRewardDetailsActivity.class));
        if (generalFunc.isRTLmode()) {
            btnAchieved.setBackground(getResources().getDrawable(R.drawable.right_curve_card));
            btnNextLevel.setBackground(getResources().getDrawable(R.drawable.left_curve_card));
            ivArrowView.setRotation(180);
        }
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    private Context getActContext() {
        return DriverRewardActivity.this;
    }

    private void charView() {
        llChartContainerView = findViewById(R.id.llChartContainerView);

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @SuppressLint("InflateParams") View view = inflater.inflate(R.layout.pie_chart_view, null);
        PieChart pieChart = (PieChart) view.findViewById(R.id.pieChart);
        view.setLayoutParams(new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.MATCH_PARENT));

        SpannableStringBuilder builder = new SpannableStringBuilder();

        String completetrip = generalFunc.getJsonValueStr("completed_trip", rewardObj);
        SpannableString redSpannable = new SpannableString(completetrip);
        redSpannable.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.appThemeColor_1)), 0, completetrip.length(), 0);
        builder.append(redSpannable);

        String totaltrip = "\n" + generalFunc.retrieveLangLBl("", "LBL_OF_TXT") + " " + generalFunc.getJsonValueStr("Total_trip", rewardObj) + " " + generalFunc.retrieveLangLBl("", "LBL_TRIP");
        SpannableString whiteSpannable = new SpannableString(totaltrip);
        if (Utils.checkText(generalFunc.getJsonValueStr("Total_trip", rewardObj))) {
            llChartContainerView.setVisibility(View.VISIBLE);
        } else {
            llChartContainerView.setVisibility(View.GONE);
        }
        whiteSpannable.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.black)), 0, totaltrip.length(), 0);
        builder.append(whiteSpannable);
        pieChart.setTransparentCircleRadius(61f);
        pieChart.setCenterText(builder);
        pieChart.setUsePercentValues(false);
        pieChart.setRotationEnabled(true);
        pieChart.setTouchEnabled(false);
        pieChart.setEnabled(false);
        pieChart.getLegend().setEnabled(false);

        pieChart.setDrawEntryLabels(false);
        pieChart.getDescription().setEnabled(false);
        pieChart.setHoleRadius(85);
        pieChart.setCenterTextSize(20f);

        if (llChartContainerView.getChildCount() > 0) {
            llChartContainerView.removeAllViewsInLayout();
        }

        llChartContainerView.addView(view);

        ArrayList<PieEntry> values = new ArrayList<>();

        values.add(new PieEntry(Float.parseFloat(generalFunc.getJsonValueStr("completed_trip_percentage", rewardObj)), ""));
        values.add(new PieEntry(Float.parseFloat(generalFunc.getJsonValueStr("uncompleted_trip_percentage", rewardObj)), ""));

        ArrayList<Integer> colors = new ArrayList<>();
        colors.add(getResources().getColor(R.color.appThemeColor_1));
        colors.add(getResources().getColor(R.color.cardView23ProBG));

        PieDataSet set1;

        if (pieChart.getData() != null && pieChart.getData().getDataSetCount() > 0) {
            set1 = (PieDataSet) pieChart.getData().getDataSetByIndex(0);
            set1.setValues(values);
            pieChart.getData().notifyDataChanged();
            pieChart.notifyDataSetChanged();
        } else {
            set1 = new PieDataSet(values, "");
            set1.setDrawValues(false);
            set1.setColors(colors);
            set1.setValueLineVariableLength(false);

            PieData data = new PieData(set1);
            pieChart.setData(data);
        }
    }

    @SuppressLint("UseCompatLoadingForColorStateLists")
    private void dataListView() {
        LinearLayout rewardDetailDisplayArea = findViewById(R.id.rewardDetailDisplayArea);
        rewardDetailDisplayArea.removeAllViewsInLayout();

        JSONArray rewardDetails = generalFunc.getJsonArray(generalFunc.getJsonValueStr("reward_details", rewardObj));
        if (rewardDetails != null) {
            for (int j = 0; j < rewardDetails.length(); j++) {

                LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                @SuppressLint("InflateParams") View convertView = infalInflater.inflate(R.layout.design_reward_detail_row, null);

                MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
                MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);
                MTextView rewardStatus = (MTextView) convertView.findViewById(R.id.rewardStatus);
                LinearLayout ll_detail_row = (LinearLayout) convertView.findViewById(R.id.ll_reward_detail_row);


                JSONObject jobject = generalFunc.getJsonObject(rewardDetails, j);

                titleHTxt.setText(generalFunc.getJsonValueStr("vTitle", jobject));
                titleVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vValue", jobject)));
                rewardStatus.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TEXT", jobject)));
                if (generalFunc.getJsonValueStr("is_completed", jobject).equalsIgnoreCase("Yes")) {
                    ll_detail_row.setBackgroundTintList(getResources().getColorStateList(R.color.green));
                    rewardStatus.setTextColor(getResources().getColorStateList(R.color.green));
                } else {
                    ll_detail_row.setBackgroundTintList(getResources().getColorStateList(R.color.red));
                    rewardStatus.setTextColor(getResources().getColorStateList(R.color.red));
                }

                rewardDetailDisplayArea.addView(convertView);
            }
        }
    }

    private void howItWork() {
        MTextView txtHowItWorks = findViewById(R.id.txtHowItWorks);
        txtHowItWorks.setText(generalFunc.retrieveLangLBl("", "LBL_HOW_IT_WORKS_TXT"));
        Bundle bn = new Bundle();
        bn.putString("response", responseStr);
        txtHowItWorks.setOnClickListener(v -> {
                    if (Utils.checkText(responseStr)) {
                        new ActUtils(getActContext()).startActWithData(DriverRewardDetailsActivity.class, bn);
                    } else {
                        generalFunc.showError();
                    }
                }
        );
    }

    public void onClick(View view) {
        int i = view.getId();
        if (isLoading) {
            return;
        }
        if (i == btnAchieved.getId()) {
            levelTag = "Achieved";
            btnAchieved.setBackgroundTintList(ColorStateList.valueOf(getResources().getColor(R.color.appThemeColor_1)));
            btnNextLevel.setBackgroundTintList(ColorStateList.valueOf(getResources().getColor(R.color.white)));
            btnAchieved.setTextColor(getResources().getColor(R.color.white));
            btnNextLevel.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            getData();
        } else if (i == btnNextLevel.getId()) {
            levelTag = "Next";
            btnNextLevel.setBackgroundTintList(ColorStateList.valueOf(getResources().getColor(R.color.appThemeColor_1)));
            btnAchieved.setBackgroundTintList(ColorStateList.valueOf(getResources().getColor(R.color.white)));
            btnNextLevel.setTextColor(getResources().getColor(R.color.white));
            btnAchieved.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            getData();
        }
    }
}