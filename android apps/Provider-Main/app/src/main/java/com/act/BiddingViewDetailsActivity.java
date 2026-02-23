package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Paint;
import android.os.Bundle;
import android.text.InputType;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.general.SkeletonViewHandler;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.WKWebView;
import com.view.editBox.MaterialEditText;

import org.json.JSONObject;

import java.util.HashMap;

public class BiddingViewDetailsActivity extends ParentActivity {

    private MTextView contactName, sourceAddressTxt, txtTaskValue, bidDateVTxt, statusVTxt, txtFinalAmountStatus, txtReOffer, txtStartTask;
    private MTextView serviceNameTxt, txtBidAmount, txtBidAmountDesc, txtReOfferAmountDesc, offerAmountTxt, commentHTxt, commentVTxt, attachedMediaTxt, vReasonVTxt, txtBiddingStatus;
    private MTextView txtAccept, txtDecline;
    private View viewAcceptDecline;
    private LinearLayout llMainArea, llButtonView, cancelReasonArea, biddingStatusArea;
    private ImageView imgChat;
    private String iBiddingPostId, iUserId;
    private AlertDialog declineAlertDialog, mReOfferDialog;
    private String required_str = "";
    private boolean isViewOnly;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_bidding_view_details);

        iBiddingPostId = getIntent().getStringExtra("iBiddingPostId");
        isViewOnly = getIntent().getBooleanExtra("isViewOnly", false);

        ImageView backImgView = findViewById(R.id.backImgView);
        backImgView.setOnClickListener(v -> finish());

        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_DETAILS_TXT"));

        llMainArea = findViewById(R.id.llMainArea);
        llMainArea = findViewById(R.id.llMainArea);
        imgChat = findViewById(R.id.imgChat);
        imgChat.setOnClickListener(v -> {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(iUserId)
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setTripId(iBiddingPostId)
                    .setBid(true)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.CHAT);
        });

        setTopDataAndTaskData();
        mainData();
        buttonView();
        cancelView();

        getBiddingViewDetailsList();
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    private void buttonView() {
        llButtonView = findViewById(R.id.llButtonView);
        statusVTxt = findViewById(R.id.statusVTxt);
        txtFinalAmountStatus = findViewById(R.id.txtFinalAmountStatus);
        viewAcceptDecline = findViewById(R.id.viewAcceptDecline);

        txtAccept = findViewById(R.id.txtAccept);
        txtAccept.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));
        txtAccept.setOnClickListener(v -> {
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                generateAlert.closeAlertBox();
                if (btn_id == 1) {
                    updateDriverBiddingStatus("Accepted", "", "");
                }
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_ACCEPT_BIDDING_TASK_TXT"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
            generateAlert.showAlertBox();
        });

        txtDecline = findViewById(R.id.txtDecline);
        txtDecline.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));
        txtDecline.setOnClickListener(v -> declineTaskDialog());

        txtReOffer = findViewById(R.id.txtReOffer);
        txtReOffer.setText(generalFunc.retrieveLangLBl("", "LBL_RE_OFFER_TXT"));
        txtReOffer.setOnClickListener(v -> reOfferDialog());

        txtStartTask = findViewById(R.id.txtStartTask);
        txtStartTask.setText(generalFunc.retrieveLangLBl("", "LBL_START_TASK_TXT"));
        txtStartTask.setOnClickListener(v -> onStartTripBtn());
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
    }

    private void setTopDataAndTaskData() {
        contactName = findViewById(R.id.contactName);
        MTextView sourceAddressHTxt = findViewById(R.id.sourceAddressHTxt);
        sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_SERVICE_ADDRESS_TXT"));
        sourceAddressTxt = findViewById(R.id.sourceAddressTxt);

        MTextView txtTaskHint = findViewById(R.id.txtTaskHint);
        txtTaskHint.setText(generalFunc.retrieveLangLBl("", "LBL_TASK_TXT"));
        txtTaskValue = findViewById(R.id.txtTaskValue);
        bidDateVTxt = findViewById(R.id.bidDateVTxt);
    }

    private void mainData() {
        serviceNameTxt = findViewById(R.id.serviceNameTxt);
        txtBidAmount = findViewById(R.id.txtBidAmount);
        txtBidAmountDesc = findViewById(R.id.txtBidAmountDesc);
        txtReOfferAmountDesc = findViewById(R.id.txtReOfferAmountDesc);
        offerAmountTxt = findViewById(R.id.offerAmountTxt);
        commentHTxt = findViewById(R.id.commentHTxt);
        commentVTxt = findViewById(R.id.commentVTxt);
        attachedMediaTxt = findViewById(R.id.attachedMediaTxt);
    }

    private void cancelView() {
        cancelReasonArea = findViewById(R.id.cancelReasonArea);
        MTextView vReasonTitleTxt = findViewById(R.id.vReasonTitleTxt);
        vReasonTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_REASON"));
        vReasonVTxt = findViewById(R.id.vReasonVTxt);

        biddingStatusArea = findViewById(R.id.biddingStatusArea);
        txtBiddingStatus = findViewById(R.id.txtBiddingStatus);
    }

    public Context getActContext() {
        return BiddingViewDetailsActivity.this;
    }

    private void goneView() {
        llMainArea.setVisibility(View.GONE);
        imgChat.setVisibility(View.GONE);
        txtBidAmountDesc.setVisibility(View.GONE);
        txtReOfferAmountDesc.setVisibility(View.GONE);
        statusVTxt.setVisibility(View.GONE);
        txtFinalAmountStatus.setVisibility(View.GONE);
        viewAcceptDecline.setVisibility(View.GONE);
        txtAccept.setVisibility(View.GONE);
        txtDecline.setVisibility(View.GONE);
        txtReOffer.setVisibility(View.GONE);
        txtStartTask.setVisibility(View.GONE);
        cancelReasonArea.setVisibility(View.GONE);

        biddingStatusArea.setVisibility(View.GONE);

        offerAmountTxt.setVisibility(View.GONE);
        commentHTxt.setVisibility(View.GONE);
        commentVTxt.setVisibility(View.GONE);
        attachedMediaTxt.setVisibility(View.GONE);
    }

    @SuppressLint("SetTextI18n")
    public void getBiddingViewDetailsList() {
        goneView();
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(findViewById(R.id.contentArea), R.layout.skeleton_task_detail);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getBiddingPostInfo");
        parameters.put("iBiddingPostId", iBiddingPostId);

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {

                    JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

                    if (responseStringObject != null && !responseStringObject.equals("")) {
                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                            llMainArea.setVisibility(View.VISIBLE);

                            String messageJson = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);

                            String showChatIcon = generalFunc.getJsonValue("showChatIcon", messageJson);
                            if (showChatIcon.equalsIgnoreCase("Yes")) {
                                imgChat.setVisibility(View.VISIBLE);
                            } else {
                                imgChat.setVisibility(View.GONE);
                            }

                            iUserId = generalFunc.getJsonValue("iUserId", messageJson);

                            if (generalFunc.getJsonValue("showAcceptBtn", messageJson).equalsIgnoreCase("Yes")) {
                                txtAccept.setVisibility(View.VISIBLE);
                            }
                            if (generalFunc.getJsonValue("showDeclineBtn", messageJson).equalsIgnoreCase("Yes")) {
                                txtDecline.setVisibility(View.VISIBLE);
                            }
                            if (txtAccept.getVisibility() == View.VISIBLE && txtDecline.getVisibility() == View.VISIBLE) {
                                viewAcceptDecline.setVisibility(View.VISIBLE);
                            }

                            if (generalFunc.getJsonValue("showStartTaskBtn", messageJson).equalsIgnoreCase("Yes")) {
                                txtStartTask.setVisibility(View.VISIBLE);
                            }
                            if (generalFunc.getJsonValue("showReOfferBtn", messageJson).equalsIgnoreCase("Yes")) {
                                txtReOffer.setVisibility(View.VISIBLE);
                            }

                            contactName.setText(generalFunc.getJsonValue("Name", messageJson));
                            sourceAddressTxt.setText(generalFunc.getJsonValue("vServiceAddress", messageJson));

                            txtTaskValue.setText(" #" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vBiddingPostNo", messageJson)));
                            /*bidDateVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBiddingDate", messageJson),
                                    Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));*/
                            bidDateVTxt.setText(generalFunc.getJsonValue("tDisplayDateTime", messageJson));

                            serviceNameTxt.setText(generalFunc.getJsonValue("vServiceName", messageJson));
                            txtBidAmount.setText(generalFunc.getJsonValue("biddingReofferAmountTitle", messageJson) + ": " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("biddingReofferAmount", messageJson)));

                            String mBiddingAmount = generalFunc.getJsonValue("biddingAmount", messageJson);
                            if (Utils.checkText(mBiddingAmount)) {
                                offerAmountTxt.setVisibility(View.VISIBLE);
                                offerAmountTxt.setText(generalFunc.getJsonValue("biddingAmountTitle", messageJson) + ": " + generalFunc.convertNumberWithRTL(mBiddingAmount));
                            }

                            String bidAmountDesc = generalFunc.getJsonValue("description_user", messageJson);
                            if (Utils.checkText(bidAmountDesc)) {
                                txtBidAmountDesc.setVisibility(View.VISIBLE);
                                txtBidAmountDesc.setText("(" + GeneralFunctions.fromHtml(bidAmountDesc) + ")");
                                generalFunc.makeTextViewResizable(txtBidAmountDesc, 2, "...\n+ " + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);
                            }

                            String reOfferAmountDesc = generalFunc.getJsonValue("description_driver", messageJson);
                            if (Utils.checkText(reOfferAmountDesc)) {
                                txtReOfferAmountDesc.setVisibility(View.VISIBLE);
                                txtReOfferAmountDesc.setText("(" + GeneralFunctions.fromHtml(reOfferAmountDesc) + ")");
                                generalFunc.makeTextViewResizable(txtReOfferAmountDesc, 2, "...\n+ " + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);
                            }


                            String mDescription = generalFunc.getJsonValue("tDescription", messageJson);
                            if (Utils.checkText(mDescription)) {
                                commentHTxt.setVisibility(View.VISIBLE);
                                commentHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DETAILS"));

                                commentVTxt.setVisibility(View.VISIBLE);
                                commentVTxt.setText(GeneralFunctions.fromHtml(mDescription));
                                generalFunc.makeTextViewResizable(commentVTxt, 2, "...\n+ " + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);
                            }

                            if (generalFunc.getJsonValue("isMediaUploaded", messageJson).equalsIgnoreCase("Yes")) {
                                attachedMediaTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ATTACHED_MEDIA_FILES_TEXT"));
                                attachedMediaTxt.setVisibility(View.VISIBLE);
                                attachedMediaTxt.setOnClickListener(v -> {
                                    Bundle bn = new Bundle();
                                    bn.putString("iBiddingPostId", iBiddingPostId);
                                    new ActUtils(getActContext()).startActWithData(BiddingAdditionalMediaActivity.class, bn);
                                });
                                attachedMediaTxt.setPaintFlags(Paint.UNDERLINE_TEXT_FLAG);
                            }

                            String eStatusMsg = generalFunc.getJsonValue("eStatusMsg", messageJson);
                            if (Utils.checkText(eStatusMsg)) {
                                statusVTxt.setVisibility(View.VISIBLE);
                                statusVTxt.setText(eStatusMsg);
                            }

                            String biddingConfirmAmount = generalFunc.getJsonValue("biddingConfirmAmount", messageJson);
                            if (Utils.checkText(biddingConfirmAmount)) {
                                txtFinalAmountStatus.setVisibility(View.VISIBLE);
                                txtFinalAmountStatus.setText(biddingConfirmAmount);
                                txtFinalAmountStatus.setText(generalFunc.getJsonValue("biddingfinalAmountTitle", messageJson) + ": " + generalFunc.convertNumberWithRTL(biddingConfirmAmount));
                            }

                            String cancelReason = generalFunc.getJsonValue("cancelReason", messageJson);
                            if (Utils.checkText(cancelReason)) {
                                cancelReasonArea.setVisibility(View.VISIBLE);
                                vReasonVTxt.setText(cancelReason);
                            }

                            String biddingWalletMsg = generalFunc.getJsonValue("biddingWalletMsg", messageJson);
                            if (Utils.checkText(biddingWalletMsg)) {
                                biddingStatusArea.setVisibility(View.VISIBLE);
                                txtBiddingStatus.setText(biddingWalletMsg);
                            }

                            boolean isBtnView = statusVTxt.getVisibility() == View.GONE && txtFinalAmountStatus.getVisibility() == View.GONE && txtDecline.getVisibility() == View.GONE && txtAccept.getVisibility() == View.GONE && txtReOffer.getVisibility() == View.GONE && txtStartTask.getVisibility() == View.GONE;
                            if (isViewOnly || isBtnView) {
                                llButtonView.setVisibility(View.GONE);
                            }
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                    SkeletonViewHandler.getInstance().hideSkeletonView();
                });
    }

    private void updateDriverBiddingStatus(String eStatus, String reOfferAmount, String offerDescription) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateBiddingStatus");
        parameters.put("iBiddingPostId", iBiddingPostId);
        parameters.put("eStatus", eStatus);

        if (eStatus.equalsIgnoreCase("Reoffer")) {
            parameters.put("amount", reOfferAmount);
            parameters.put("description", offerDescription);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    if (responseString != null && !responseString.equals("")) {

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), button_Id -> {
                                // Done
                                if (eStatus.equalsIgnoreCase("Decline")) {
                                    finish();
                                    return;
                                }
                                getBiddingViewDetailsList();
                            });
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }

    private void declineTaskDialog() {
        if (declineAlertDialog != null && declineAlertDialog.isShowing()) {
            return;
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.desgin_decline_task, null);
        builder.setView(dialogView);

        MTextView txtReOffer = dialogView.findViewById(R.id.txtReOffer);
        MTextView txtDecline = dialogView.findViewById(R.id.txtDecline);
        MTextView titileTxt = dialogView.findViewById(R.id.titileTxt);
        WKWebView msgTxt = dialogView.findViewById(R.id.msgTxt);
        titileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TASK"));
        txtReOffer.setText(generalFunc.retrieveLangLBl("", "LBL_RE_OFFER_TXT"));
        txtDecline.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));
        msgTxt.loadData(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TASK_CONFIRM_MSG"), WKWebView.ContentType.ALERT_DIALOG);
        txtReOffer.setOnClickListener(view -> {
            declineAlertDialog.dismiss();
            reOfferDialog();
        });
        txtDecline.setOnClickListener(view -> {
            declineAlertDialog.dismiss();
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                generateAlert.closeAlertBox();
                if (btn_id == 1) {
                    updateDriverBiddingStatus("Decline", "", "");
                }
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_DECLINE_BIDDING_TASK_TXT"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
            generateAlert.showAlertBox();
        });
        declineAlertDialog = builder.create();
        declineAlertDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(declineAlertDialog);
        declineAlertDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        declineAlertDialog.show();
    }

    private void reOfferDialog() {
        if (mReOfferDialog != null && mReOfferDialog.isShowing()) {
            return;
        }
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dialog_bidding_re_offre, null);
        builder.setView(dialogView);

        final MaterialEditText editBoxReOfferAmount = dialogView.findViewById(R.id.editBoxReOfferAmount);
        editBoxReOfferAmount.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        editBoxReOfferAmount.setHint(generalFunc.retrieveLangLBl("", "LBL_YOUR_OFFER_TXT"));
        editBoxReOfferAmount.setHideUnderline(true);
        editBoxReOfferAmount.setGravity(Gravity.START | Gravity.CENTER);
        editBoxReOfferAmount.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        editBoxReOfferAmount.setIncludeFontPadding(false);
        editBoxReOfferAmount.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.border));
        if (generalFunc.isRTLmode()) {
            editBoxReOfferAmount.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            editBoxReOfferAmount.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }

        final MTextView txtTitle = dialogView.findViewById(R.id.txtTitle);
        txtTitle.setText(generalFunc.retrieveLangLBl("", "LBL_RE_OFFER_TXT"));

        final MaterialEditText editBoxDescription = dialogView.findViewById(R.id.editBoxDescription);
        editBoxDescription.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        editBoxDescription.setSingleLine(false);
        editBoxDescription.setHideUnderline(true);
        editBoxDescription.setGravity(Gravity.START | Gravity.TOP);
        editBoxDescription.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        editBoxDescription.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.border));
        if (generalFunc.isRTLmode()) {
            editBoxDescription.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            editBoxDescription.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }
        editBoxDescription.setIncludeFontPadding(false);
        editBoxDescription.setHint(generalFunc.retrieveLangLBl("", "LBL_DESCRIPTION"));

        final MTextView txtOk = dialogView.findViewById(R.id.txtOk);
        txtOk.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        txtOk.setOnClickListener(view -> {
            boolean reOfferAmount = Utils.checkText(editBoxReOfferAmount) || Utils.setErrorFields(editBoxReOfferAmount, required_str);
            //boolean offerDescription = Utils.checkText(editBoxDescription) || Utils.setErrorFields(editBoxDescription, required_str);
            if (!reOfferAmount /*|| !offerDescription*/) {
                return;
            }
            Utils.hideKeyboard(getActContext());
            mReOfferDialog.dismiss();
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                generateAlert.closeAlertBox();
                if (btn_id == 1) {
                    updateDriverBiddingStatus("Reoffer", Utils.getText(editBoxReOfferAmount), Utils.getText(editBoxDescription));
                }
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_RE_OFFER_BIDDING_TASK_TXT"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
            generateAlert.showAlertBox();

        });

        final MTextView txtCancel = dialogView.findViewById(R.id.txtCancel);
        txtCancel.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        txtCancel.setOnClickListener(view -> {
            Utils.hideKeyboard(getActContext());
            mReOfferDialog.dismiss();
        });

        mReOfferDialog = builder.create();
        mReOfferDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(mReOfferDialog);
        mReOfferDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        mReOfferDialog.show();
    }

    private void onStartTripBtn() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            generateAlert.closeAlertBox();
            if (btn_id == 1) {
                startTask();
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_START_BIDDING_TASK_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
        generateAlert.showAlertBox();
    }

    private void startTask() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverBiddingTaskStatus");
        parameters.put("iBiddingPostId", iBiddingPostId);
        parameters.put("vTaskStatus", "Active");

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {
                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                            MyApp.getInstance().restartWithGetDataApp();
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }
}