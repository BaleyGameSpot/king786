package com.act;

import android.annotation.SuppressLint;
import android.net.Uri;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fragments.ChatDataFragment;
import com.general.files.FileSelector;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityChatBinding;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

@SuppressLint("all")
public class ChatActivity extends ParentActivity {

    private ActivityChatBinding binding;

    private final HashMap<String, ChatDataFragment> fragMap = new HashMap<>();
    ArrayList<String> fragTagsLst = new ArrayList<>();

    public ChatDataFragment currentVisibleFrag;

    public HashMap<String, String> dataMap;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_chat);

        continueExecution(savedInstanceState);
    }


    @Override
    protected void onRestoreInstanceState(@NonNull Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
    }

    private HashMap<String, String> getData(Bundle savedInstanceState) {

        HashMap<String, String> dataMap = new HashMap<>();

        if (savedInstanceState != null && savedInstanceState.containsKey("RESTART_STATE") && savedInstanceState.getString("RESTART_STATE").equalsIgnoreCase("true")) {
            dataMap.put("iTripId", savedInstanceState.getString("iTripId") != null ? savedInstanceState.getString("iTripId") : "");
            dataMap.put("iBiddingPostId", savedInstanceState.getString("iBiddingPostId") != null ? savedInstanceState.getString("iBiddingPostId") : "");
            dataMap.put("vBookingNo", savedInstanceState.getString("vBookingNo") != null ? savedInstanceState.getString("vBookingNo") : "");
            dataMap.put("iOrderId", savedInstanceState.getString("iOrderId") != null ? savedInstanceState.getString("iOrderId") : "");
            dataMap.put("iToMemberType", savedInstanceState.getString("iToMemberType") != null ? savedInstanceState.getString("iToMemberType") : "");
            dataMap.put("iToMemberId", savedInstanceState.getString("iToMemberId") != null ? savedInstanceState.getString("iToMemberId") : "");

            dataMap.put("isForPickupPhotoRequest", savedInstanceState.getString("isForPickupPhotoRequest"));
            dataMap.put("isOpenMediaDialog", savedInstanceState.getString("isOpenMediaDialog"));
        } else if (getIntent() != null) {
            dataMap.put("iTripId", getIntent().getStringExtra("iTripId") != null ? getIntent().getStringExtra("iTripId") : "");
            dataMap.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId") != null ? getIntent().getStringExtra("iBiddingPostId") : "");
            dataMap.put("vBookingNo", getIntent().getStringExtra("vBookingNo") != null ? getIntent().getStringExtra("vBookingNo") : "");
            dataMap.put("iOrderId", getIntent().getStringExtra("iOrderId") != null ? getIntent().getStringExtra("iOrderId") : "");
            dataMap.put("iToMemberType", getIntent().getStringExtra("iToMemberType") != null ? getIntent().getStringExtra("iToMemberType") : "");
            dataMap.put("iToMemberId", getIntent().getStringExtra("iToMemberId") != null ? getIntent().getStringExtra("iToMemberId") : "");

            dataMap.put("isForPickupPhotoRequest", getIntent().getStringExtra("isForPickupPhotoRequest"));
            dataMap.put("isOpenMediaDialog", getIntent().getStringExtra("isOpenMediaDialog"));
        } else {
            return null;
        }

        return dataMap;
    }

    private void continueExecution(Bundle savedInstanceState) {
        dataMap = getData(savedInstanceState);

        replaceFragment(dataMap, savedInstanceState);
    }

    private void replaceFragment(HashMap<String, String> dataMap, Bundle savedInstanceState) {
        if (dataMap == null) {
            generalFunc.showError(true);
            return;
        }

        String iBiddingPostId = dataMap.get("iBiddingPostId");
        String iToMemberId = dataMap.get("iToMemberId");
        String iToMemberType = dataMap.get("iToMemberType");
        String iTripId = dataMap.get("iTripId");
        String iOrderId = dataMap.get("iOrderId");

        if (currentVisibleFrag != null) {

            boolean isSameBidTask = Utils.checkText(iBiddingPostId) && currentVisibleFrag.dataMap.get("iBiddingPostId").equalsIgnoreCase(iBiddingPostId) && currentVisibleFrag.dataMap.get("iToMemberId").equalsIgnoreCase(iToMemberId);

            boolean isSameService = !Utils.checkText(iBiddingPostId) && Utils.checkText(iTripId) && currentVisibleFrag.dataMap.get("iTripId").equalsIgnoreCase(iTripId);

            boolean isSameOrder = Utils.checkText(iOrderId) && currentVisibleFrag.dataMap.get("iOrderId").equalsIgnoreCase(iOrderId) && currentVisibleFrag.dataMap.get("iToMemberType").equalsIgnoreCase(iToMemberType);

            if (isSameBidTask || isSameService || isSameOrder) {
                currentVisibleFrag.handleIncomingMessages(dataMap);
                return;
            }
        }

        String frag_tag;
        if (Utils.checkText(iBiddingPostId)) {
            frag_tag = "ServiceBid_" + iBiddingPostId + "_" + iToMemberId;
        } else if (Utils.checkText(iTripId)) {
            frag_tag = "Service_" + iTripId;
        } else if (Utils.checkText(iOrderId)) {
            frag_tag = "Order_" + iOrderId + "_" + iToMemberType + "_" + iToMemberId;
        } else {
            frag_tag = "Service";
        }

        ChatDataFragment chatDataFrag = null;
        if (fragMap.get(frag_tag) != null) {
            chatDataFrag = fragMap.get(frag_tag);
            fragMap.remove(frag_tag);
        } else {
            if (savedInstanceState != null && savedInstanceState.containsKey("RESTART_STATE") && savedInstanceState.getString("RESTART_STATE").equalsIgnoreCase("true")) {

            } else {
                chatDataFrag = new ChatDataFragment(dataMap);
            }
        }

        String finalFrag_tag = frag_tag;
        fragTagsLst.removeIf(value -> value.equalsIgnoreCase(finalFrag_tag));
        fragTagsLst.add(frag_tag);


        if (chatDataFrag != null) {
            fragMap.put(frag_tag, chatDataFrag);
            this.currentVisibleFrag = chatDataFrag;
            getSupportFragmentManager().beginTransaction().replace(binding.fragContainer.getId(), chatDataFrag, frag_tag).commit();
        }
    }

    @Override
    public void onBackPressed() {

        if (currentVisibleFrag != null) {
            if (currentVisibleFrag.getFullImageView()) {
                return;
            }
        }

        int frag_size = fragMap.size();
        if (frag_size <= 1) {
            super.getOnBackPressedDispatcher().onBackPressed();
        } else {
            if (currentVisibleFrag != null) {
                fragMap.remove(currentVisibleFrag.getTag());
            }
            if (!fragTagsLst.isEmpty()) {
                fragTagsLst.remove(fragTagsLst.size() - 1);
            }

            currentVisibleFrag = null;

            if (!fragTagsLst.isEmpty()) {
                String lastTag = fragTagsLst.get(fragTagsLst.size() - 1);
                ChatDataFragment frag = fragMap.get(lastTag);
                if (frag != null) {
                    replaceFragment(frag.dataMap, null);
                }
            }
        }
    }

    public void handleIncomingMessages(JSONObject obj_data) {
        replaceFragment(new Gson().fromJson(obj_data.toString(), new TypeToken<HashMap<String, String>>() {
        }.getType()), null);
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (currentVisibleFrag != null) {
            currentVisibleFrag.onFileSelected(mFileUri, mFilePath, mFileType);
        }
    }
}