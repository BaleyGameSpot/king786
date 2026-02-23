package com.general.files;

import android.content.Context;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.view.LayoutInflater;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;

import com.buddyverse.main.databinding.AgeConfirmDialogBinding;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.view.MButton;
import com.view.MaterialRippleLayout;

public class showTermsDialog {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final int pos;
    private final String service;
    private boolean click, hideCloseView = false;
    private AlertDialog alertDialog;
    private final OnClickList clickListener;

    public showTermsDialog(Context mContext, GeneralFunctions generalFunc, int position, String service, boolean click, OnClickList clickListener) {
        this.generalFunc = generalFunc;
        this.pos = position;
        this.service = service;
        this.click = click;
        this.mContext = mContext;
        this.clickListener = clickListener;
        showDialog();
    }

    public showTermsDialog(Context mContext, GeneralFunctions generalFunc, int position, String service, boolean click, OnClickList clickListener, boolean hideCloseView) {
        this.generalFunc = generalFunc;
        this.pos = position;
        this.service = service;
        this.click = click;
        this.mContext = mContext;
        this.clickListener = clickListener;
        this.hideCloseView = hideCloseView;
        showDialog();
    }

    private void showDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull AgeConfirmDialogBinding binding = AgeConfirmDialogBinding.inflate(inflater, null, false);

        binding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AGE_CONFIRMATION"));
        binding.confirmTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AGE_NOTE"));
        binding.confirmTxt.setOnClickListener(view -> binding.confirmChkBox.setChecked(!binding.confirmChkBox.isChecked()));

        binding.cancelImg.setVisibility(hideCloseView ? View.GONE : View.VISIBLE);
        binding.cancelImg.setOnClickListener(v -> alertDialog.dismiss());

        MButton btn_confirm = ((MaterialRippleLayout) binding.btnConfirm).getChildView();
        btn_confirm.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
        btn_confirm.setEnabled(false);
        btn_confirm.setOnClickListener(v -> {
            if (click) {
                CommonUtilities.ageRestrictServices.add("1");
                alertDialog.dismiss();

                if (clickListener != null) {
                    clickListener.onClick();
                }
            }
        });

        binding.confirmChkBox.setOnCheckedChangeListener((compoundButton, b) -> {
            if (b) {
                click = true;
                btn_confirm.setEnabled(true);
            } else {
                click = false;
                btn_confirm.setEnabled(false);
            }
        });
        builder.setView(binding.getRoot());
        alertDialog = builder.create();
        LayoutDirection.setLayoutDirection(alertDialog);
        alertDialog.setCancelable(false);
        alertDialog.setCanceledOnTouchOutside(false);
        alertDialog.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        alertDialog.show();
    }

    public interface OnClickList {
        void onClick();
    }
}