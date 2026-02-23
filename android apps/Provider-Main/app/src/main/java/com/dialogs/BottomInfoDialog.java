package com.dialogs;

import android.app.Activity;
import android.content.Context;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.appcompat.app.AlertDialog;

import com.airbnb.lottie.LottieAnimationView;
import com.general.files.GeneralFunctions;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.providers.R;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.MTextView;
import com.view.WKWebView;

public class BottomInfoDialog {

    private final Context actContext;
    private final GeneralFunctions generalFunc;
    private BottomSheetDialog hotoUseDialog;

    public BottomInfoDialog(Context actContext, GeneralFunctions generalFunc) {
        this.actContext = actContext;
        this.generalFunc = generalFunc;
    }

    public void showPreferenceDialog(String title, String Msg, int img, Boolean isUpload, String posBtn, String negBtn, boolean isanimation) {
        AlertDialog.Builder builder = new AlertDialog.Builder(actContext);

        View dialogView = View.inflate(actContext, R.layout.design_bottom_info, null);
        builder.setView(dialogView);
        ImageView iamage_source = dialogView.findViewById(R.id.iamage_source);
        LinearLayout uploadArea = dialogView.findViewById(R.id.uploadArea);
        MTextView uploadTitleTxt = dialogView.findViewById(R.id.uploadTitleTxt);
        View imageArea = dialogView.findViewById(R.id.imageArea);
        View animationArea = dialogView.findViewById(R.id.animationArea);
        LottieAnimationView animationView = dialogView.findViewById(R.id.animationView);
        if (isanimation) {
            animationView.setAnimation(img);
            animationArea.setVisibility(View.VISIBLE);
            imageArea.setVisibility(View.GONE);
        } else {
            imageArea.setVisibility(View.VISIBLE);
            animationArea.setVisibility(View.GONE);
        }

        uploadTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_ITEM_PHOTO"));

        if (img != 0) {
            iamage_source.setImageResource(img);
        }
        if (isUpload) {
            uploadArea.setVisibility(View.VISIBLE);
        }


        MTextView okTxt = dialogView.findViewById(R.id.okTxt);
        MTextView skipTxtArea = dialogView.findViewById(R.id.skipTxtArea);
        MTextView titileTxt = dialogView.findViewById(R.id.titileTxt);
        WKWebView msgTxt = dialogView.findViewById(R.id.msgTxt);
        if (!title.equalsIgnoreCase("")) {
            titileTxt.setText(title);
            titileTxt.setVisibility(View.VISIBLE);
        } else {
            titileTxt.setVisibility(View.GONE);
        }

        if (!posBtn.equals("")) {
            okTxt.setText(posBtn);
            okTxt.setVisibility(View.VISIBLE);
        } else {
            okTxt.setVisibility(View.GONE);
        }
        if (!negBtn.equals("")) {
            skipTxtArea.setText(negBtn);
            skipTxtArea.setVisibility(View.VISIBLE);
        } else {
            skipTxtArea.setVisibility(View.GONE);

        }
        if (Utils.checkText(Msg)) {
            msgTxt.loadData(Msg, WKWebView.ContentType.ALERT_DIALOG);
        } else {
            msgTxt.setVisibility(View.GONE);
        }

        okTxt.setOnClickListener(view -> hotoUseDialog.dismiss());
        skipTxtArea.setOnClickListener(view -> hotoUseDialog.dismiss());
        hotoUseDialog = new BottomSheetDialog(actContext);
        hotoUseDialog.setContentView(dialogView);
        View bottomSheetView = hotoUseDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(actContext.getResources().getColor(android.R.color.transparent));
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) dialogView.getParent());
        mBehavior.setPeekHeight(Utils.dpToPx(600, actContext));

        hotoUseDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(hotoUseDialog);
        Animation a = AnimationUtils.loadAnimation(actContext, R.anim.bottom_up);
        a.reset();
        bottomSheetView.clearAnimation();
        bottomSheetView.startAnimation(a);
        if (!((Activity) actContext).isFinishing()) {
            hotoUseDialog.show();
        }
    }
}