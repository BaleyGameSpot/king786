package com.general.files;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;

import com.buddyverse.providers.BuildConfig;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DialogProgressUpdateBinding;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;

public class OpenProgressUpdateDialog implements Runnable {

    private DialogProgressUpdateBinding binding;
    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final UploadProfileImage uploadProfileImage;
    public Dialog dialog_img_update;
    private final String txtMsg;

    public OpenProgressUpdateDialog(Context mContext, GeneralFunctions generalFunc, UploadProfileImage uploadProfileImage, String txtMsg) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.uploadProfileImage = uploadProfileImage;
        this.txtMsg = txtMsg;
    }

    @Override
    public void run() {
        if (!(mContext instanceof Activity)) {
            Logger.e(BuildConfig.APPLICATION_ID, "Context must be instance of Activity OR Fragment");
            return;
        }

        dialog_img_update = new Dialog(mContext, R.style.ImageSourceDialogStyle);
        binding = DialogProgressUpdateBinding.inflate(LayoutInflater.from(mContext), null, false);
        //----

        binding.cancelUpload.setOnClickListener(v -> {
            final GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                if (btn_id == 1) {
                    uploadProfileImage.cancel(true);
                } else if (btn_id == 0) {
                    generateAlert.closeAlertBox();
                }
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("Are you sure you want to cancel upload?", "LBL_SURE_CANCEL_UPLOAD"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TRIP_TXT"));
            generateAlert.showAlertBox();
        });

        binding.circularProgressbar.setProgress(0);
        binding.progressVTxt.setText("0%");
        binding.simpleProgressbar.setVisibility(View.GONE);

        binding.pleaseWaitTxt.setText(generalFunc.retrieveLangLBl("Please Wait", "LBL_PLEASE_WAIT"));
        if (Utils.checkText(txtMsg)) {
            binding.uploadingTxt.setText(txtMsg);
        }

        //----
        dialog_img_update.setContentView(binding.getRoot());

        dialog_img_update.setCanceledOnTouchOutside(false);
        dialog_img_update.setCancelable(false);
        LayoutDirection.setLayoutDirection(dialog_img_update);
        dialog_img_update.show();
    }

    @SuppressLint("SetTextI18n")
    public void updateProgress(int progress) {
        binding.progressVTxt.setText("" + progress + "%");
        binding.circularProgressbar.setProgress(progress);

        if (0 > progress || progress >= 100) {
            binding.circularProgressbar.setVisibility(View.GONE);
            binding.progressVTxt.setText("");

            binding.simpleProgressbar.setVisibility(View.VISIBLE);
            binding.cancelUpload.setVisibility(View.GONE);
        }
    }
}