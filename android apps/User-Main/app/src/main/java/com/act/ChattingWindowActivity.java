package com.act;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;

import androidx.activity.OnBackPressedCallback;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityChattingWindowBinding;
import com.livechatinc.inappchat.ChatWindowConfiguration;
import com.livechatinc.inappchat.ChatWindowErrorType;
import com.livechatinc.inappchat.ChatWindowEventsListener;
import com.livechatinc.inappchat.models.NewMessageModel;

import java.util.HashMap;

public class ChattingWindowActivity extends ParentActivity {


    private ActivityChattingWindowBinding binding;
    private OnBackPressedCallback onBackCallBack;
    private ChatWindowConfiguration chatWindowConfiguration;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_chatting_window);
        binding.headerToolBar.titleTxt.setText(generalFunc.retrieveLangLBl("Live Chat", "LBL_LIVE_CHAT"));
        addToClickHandler(binding.headerToolBar.backImgView);

        String LICENCE_NUMBER = getIntent().getStringExtra(ChatWindowConfiguration.KEY_LICENCE_NUMBER);
        String VISITOR_NAME = getIntent().getStringExtra(ChatWindowConfiguration.KEY_VISITOR_NAME);
        String VISITOR_EMAIL = getIntent().getStringExtra(ChatWindowConfiguration.KEY_VISITOR_EMAIL);
        String GROUP_ID = getIntent().getStringExtra(ChatWindowConfiguration.KEY_GROUP_ID);
        HashMap<String, String> CUSTOM_PARAM = (HashMap<String, String>) getIntent().getSerializableExtra("myParam");

        onBackCallBack = new OnBackPressedCallback(true) {
            @Override
            public void handleOnBackPressed() {
                ChattingWindowActivity.this.finish();
            }
        };
        getOnBackPressedDispatcher().addCallback(onBackCallBack);

        chatWindowConfiguration = new ChatWindowConfiguration(
                LICENCE_NUMBER,
                GROUP_ID,
                VISITOR_NAME,
                VISITOR_EMAIL,
                CUSTOM_PARAM);


        binding.embeddedChatWindow.setEventsListener(new ChatWindowEventsListener() {
            @Override
            public void onWindowInitialized() {
            }

            @Override
            public void onChatWindowVisibilityChanged(boolean visible) {
                if (!visible) {
                    onBackCallBack.handleOnBackPressed();
                }

            }

            @Override
            public void onNewMessage(NewMessageModel message, boolean windowVisible) {
            }

            @Override
            public void onStartFilePickerActivity(Intent intent, int requestCode) {
            }

            @Override
            public void onRequestAudioPermissions(String[] permissions, int requestCode) {
            }

            @Override
            public boolean onError(ChatWindowErrorType errorType, int errorCode, String errorDescription) {
                return false;
            }

            @Override
            public boolean handleUri(Uri uri) {
                return false;
            }
        });

        binding.embeddedChatWindow.setConfiguration(chatWindowConfiguration);
        binding.embeddedChatWindow.initialize();
        binding.embeddedChatWindow.showChatWindow();

    }

    @Override
    public void onClick(View view) {
        super.onClick(view);
        if (view.getId() == binding.headerToolBar.backImgView.getId()) {
            onBackCallBack.handleOnBackPressed();
        }
    }
}