package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityInviteFriendsBinding;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

public class InviteFriendsActivity extends ParentActivity {

    private ActivityInviteFriendsBinding binding;
    private MButton inviteBtn;
    private String LBL_INVITE_FRIEND_TXT;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_invite_friends);

        LBL_INVITE_FRIEND_TXT = generalFunc.retrieveLangLBl("", "LBL_INVITE_FRIEND_TXT");

        init();
    }

    private void init() {
        addToClickHandler(binding.toolbarInclude.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        binding.toolbarInclude.titleTxt.setText(LBL_INVITE_FRIEND_TXT);

        binding.shareTxtLbl.setText(generalFunc.retrieveLangLBl("", "LBL_INVITE_FRIEND_SHARE"));
        binding.shareTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("INVITE_DESCRIPTION_CONTENT", obj_userProfile)));

        binding.inviteCodeTxt.setText(generalFunc.getJsonValueStr("vRefCode", obj_userProfile).trim());
        addToClickHandler(binding.inviteCodeTxt);

        inviteBtn = ((MaterialRippleLayout) binding.inviteBtn).getChildView();
        inviteBtn.setId(Utils.generateViewId());
        inviteBtn.setText(LBL_INVITE_FRIEND_TXT);
        addToClickHandler(inviteBtn);
    }

    public void onClick(View view) {
        Utils.hideKeyboard(this);
        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();

        } else if (i == inviteBtn.getId()) {
            Intent sharingIntent = new Intent(Intent.ACTION_SEND);
            sharingIntent.setType("text/plain");
            sharingIntent.putExtra(Intent.EXTRA_SUBJECT, LBL_INVITE_FRIEND_TXT);
            sharingIntent.putExtra(Intent.EXTRA_TEXT, generalFunc.getJsonValueStr("INVITE_SHARE_CONTENT", obj_userProfile));
            startActivity(Intent.createChooser(sharingIntent, generalFunc.retrieveLangLBl("", "LBL_SHARE_USING")));

        } else if (i == binding.inviteCodeTxt.getId()) {
            android.content.ClipboardManager clipboard = (android.content.ClipboardManager) this.getSystemService(Context.CLIPBOARD_SERVICE);
            android.content.ClipData clip = android.content.ClipData.newPlainText("Copied Text", Utils.getText(binding.inviteCodeTxt));
            clipboard.setPrimaryClip(clip);
            generalFunc.showMessage(binding.shareTxt, generalFunc.retrieveLangLBl("", "LBL_INVITE_COPY_CLIPBOARD"));
        }
    }
}