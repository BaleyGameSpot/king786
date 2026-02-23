package com.act.trackService;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.Html;
import android.view.View;

import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.UberXHomeActivity;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityPairCodeGenrateBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class PairCodeGenrateActivity extends ParentActivity {

    private ActivityPairCodeGenrateBinding binding;
    private MButton btnTypeSuccess;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_pair_code_genrate);

        binding.msgTxt.setText(Html.fromHtml(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_PAIRING_PROCESS_DESC")));
        binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_PAIRING_PROCESS_TITLE"));
        binding.paircodeTitleTxt.setText(generalFunc.retrieveLangLBl("Your Pairing code", "LBL_TRACK_SERVICE_YOUR_PAIRING_TXT"));

        binding.backBtn.setVisibility(View.VISIBLE);
        addToClickHandler(binding.backBtn);
        if (generalFunc.isRTLmode()) {
            binding.backBtn.setRotation(180);
        }

        binding.copyPairCode.setVisibility(View.GONE);
        addToClickHandler(binding.copyPairCode);
        binding.sendPairCode.setVisibility(View.GONE);
        addToClickHandler(binding.sendPairCode);

        btnTypeSuccess = ((MaterialRippleLayout) binding.btnTypeSuccess).getChildView();
        btnTypeSuccess.setId(Utils.generateViewId());
        btnTypeSuccess.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        addToClickHandler(btnTypeSuccess);

        binding.sucesstitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_SETUP_PROFILE_SUCCESS_TITLE"));
        binding.sucessmsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_PROFILE_SETUP_SUCCESS_MSG"));

        binding.imgBGAlpha.setBackgroundDrawable(ContextCompat.getDrawable(this, R.drawable.ic_circle));
        binding.imgBGAlpha.getBackground().setAlpha(70);

        generatePairingCode();
    }

    private void generatePairingCode() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GeneratePairingCode");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);
        parameters.put("tSessionId", generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
        parameters.put("MemberType", getIntent().getStringExtra("MemberType"));

        ApiHandler.execute(this, parameters, responseString -> {

            binding.loaderView.setVisibility(View.GONE);
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    binding.paircodeTxt.setText(generalFunc.getJsonValue(Utils.message_str, responseString));
                    binding.copyPairCode.setVisibility(View.VISIBLE);
                    binding.sendPairCode.setVisibility(View.VISIBLE);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        });
    }

    public void pubNubMsgArrived(final String message) {

        runOnUiThread(() -> {
            String msgType = generalFunc.getJsonValue("MsgType", message);

            if (msgType.equalsIgnoreCase("TrackMemberPaired")) {

                binding.sucessArea.setVisibility(View.VISIBLE);
                binding.paircodeArea.setVisibility(View.GONE);

            }
        });
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.backBtn.getId()) {
            onBackPressed();

        } else if (i == btnTypeSuccess.getId()) {
            Bundle bn = new Bundle();
            bn.putBoolean("isRestartApp", true);
            bn.putString("MemberType", getIntent().getStringExtra("MemberType"));
            new ActUtils(this).startActWithData(TrackAnyList.class, bn);
            finishAffinity();

        } else if (i == binding.copyPairCode.getId()) {
            android.content.ClipboardManager clipboard = (android.content.ClipboardManager) this.getSystemService(Context.CLIPBOARD_SERVICE);
            android.content.ClipData clip = android.content.ClipData.newPlainText("Copied Text", Utils.getText(binding.paircodeTxt));
            clipboard.setPrimaryClip(clip);
            generalFunc.showMessage(binding.copyPairCode, generalFunc.retrieveLangLBl("Your Pairing code is copied to clipboard", "LBL_PARINING_CODE_COPY_CLIPBOARD_TXT"));

        } else if (i == binding.sendPairCode.getId()) {

            String pCode = Utils.getText(binding.paircodeTxt);
            if (Utils.checkText(pCode)) {
                Intent sharingIntent = new Intent(Intent.ACTION_SEND);
                sharingIntent.setType("text/plain");
                sharingIntent.putExtra(Intent.EXTRA_TEXT, pCode);
                startActivity(Intent.createChooser(sharingIntent, generalFunc.retrieveLangLBl("", "LBL_SHARE_USING")));
            }
        }
    }

    @Override
    public void onBackPressed() {
        if (getIntent().getBooleanExtra("isRestartApp", false)) {
            Bundle bn = new Bundle();
            new ActUtils(this).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        } else {
            super.onBackPressed();
        }
    }
}