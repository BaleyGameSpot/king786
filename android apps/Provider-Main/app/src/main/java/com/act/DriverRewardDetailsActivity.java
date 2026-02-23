package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.widget.ImageView;

import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.RewardAchieveAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.providers.R;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.HTextView;
import com.view.MTextView;
import com.view.WKWebView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONObject;

public class DriverRewardDetailsActivity extends ParentActivity {

    private JSONArray rewardsToAchieveArr;
    private RecyclerView rvRewardAchieveList;
    private MTextView rewardTitle, rewardMore, needHelpTxt;
    private HTextView rewardSubTitle;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_reward_deatails);
        initViews();

        setListData();
    }

    private void setListData() {
        rewardTitle.setText(generalFunc.retrieveLangLBl("", "LBL_REWARD_TITLE_TXT"));

        String rewardSubTitleDesc = generalFunc.getJsonValueStr("REWARD_SUBTITLE_DESC", obj_userProfile);
        if (Utils.checkText(rewardSubTitleDesc)) {
            rewardSubTitle.setText(GeneralFunctions.fromHtml(rewardSubTitleDesc));
        } else {
            rewardSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_REWARD_SUBTITLE"), 0);
//            rewardSubTitle.setText(generalFunc.retrieveLangLBl("", "LBL_REWARD_SUBTITLE"));
        }

        rewardMore.setText(generalFunc.retrieveLangLBl("", "LBL_MORE"));
        needHelpTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NEED_HELP"));
        RewardAchieveAdapter rewardAchieveAdapter = new RewardAchieveAdapter(this, generalFunc);
        rvRewardAchieveList.setAdapter(rewardAchieveAdapter);
        rewardAchieveAdapter.updateList(rewardsToAchieveArr);

    }

    private void initViews() {
        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());
        JSONObject rewardObj = generalFunc.getJsonObject("message", getIntent().getStringExtra("response"));
        rewardsToAchieveArr = generalFunc.getJsonArray(generalFunc.getJsonValueStr("rewards_to_achieve", rewardObj));

        ImageView backImgView = findViewById(R.id.backImgView);
        backImgView.setOnClickListener(v -> finish());
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.getJsonValueStr("default_reward_title", rewardObj));

        rvRewardAchieveList = findViewById(R.id.rvRewardAchieveList);
        rewardTitle = (MTextView) findViewById(R.id.rewardTitle);

        rewardSubTitle = (HTextView) findViewById(R.id.rewardSubTitle);
        rewardMore = (MTextView) findViewById(R.id.rewardmore);
        needHelpTxt = (MTextView) findViewById(R.id.needHelpTxt);
        needHelpTxt.setOnClickListener(v -> new ActUtils(getActContext()).startAct(HelpActivity23Pro.class));
        rewardMore.setOnClickListener(v -> {
            final BottomSheetDialog optionDailog = new BottomSheetDialog(getActContext());
            View contentView = View.inflate(getActContext(), R.layout.dialog_web_view, null);
            optionDailog.setContentView(contentView);
            BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
            mBehavior.setPeekHeight(Utils.dpToPx(600, getActContext()));
            optionDailog.setCancelable(false);
            optionDailog.setCanceledOnTouchOutside(false);
            View bottomSheetView = optionDailog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
            bottomSheetView.setBackgroundColor(getResources().getColor(android.R.color.transparent));

            ImageView closeImg = (ImageView) bottomSheetView.findViewById(R.id.closeImg);
            closeImg.setOnClickListener(v1 -> optionDailog.dismiss());

            AVLoadingIndicatorView loaderView = (AVLoadingIndicatorView) bottomSheetView.findViewById(R.id.loaderView);

            WKWebView mWebViewDialog = (WKWebView) bottomSheetView.findViewById(R.id.webViewDialog);
            mWebViewDialog.setWebChromeClient(new WebChromeClient() {
                @Override
                public void onProgressChanged(WebView view, int newProgress) {
                    if (newProgress > 90) {
                        loaderView.setVisibility(View.GONE);
                    }
                }
            });
            mWebViewDialog.getSettings().setJavaScriptEnabled(true);
            mWebViewDialog.loadUrl(generalFunc.getJsonValueStr("REWARD_HOW_IT_WORKS", rewardObj));
            LayoutDirection.setLayoutDirection(optionDailog);
            try {
                if (!optionDailog.isShowing()) {
                    optionDailog.show();
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        });
    }

    private Context getActContext() {
        return DriverRewardDetailsActivity.this;
    }
}