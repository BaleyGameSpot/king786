package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.content.Context;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;

import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DialogRideRatingBinding;
import com.buddyverse.main.databinding.ItemRideDetailsSummaryBinding;
import com.buddyverse.main.databinding.ItemRideProMultiStopAddressBinding;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class RideSharingUtils {
    public static void wayPointsView(Context context, GeneralFunctions generalFunc, JSONArray jsonArray, LinearLayout layout) {
        for (int k = 0; k < jsonArray.length(); k++) {
            LayoutInflater itemCartInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull ItemRideProMultiStopAddressBinding bindingItem = ItemRideProMultiStopAddressBinding.inflate(itemCartInflater, layout, false);
            JSONObject obj = generalFunc.getJsonObject(jsonArray, k);

            bindingItem.ivRound.setVisibility(View.GONE);
            bindingItem.locTagTxt.setText(generalFunc.getJsonValueStr("letterPoint", obj));
            bindingItem.addressTxt.setText(generalFunc.getJsonValueStr("address", obj));
            bindingItem.addressTxt.setPadding(0, 0, 0, (int) context.getResources().getDimension(R.dimen._10sdp));
            bindingItem.timeTxt.setText(generalFunc.getJsonValueStr("WPDate", obj));
            layout.addView(bindingItem.getRoot());
        }
    }

    public static void preferencesView(Context context, GeneralFunctions generalFunc, JSONArray jsonArray, LinearLayout layout) {
        for (int k = 0; k < jsonArray.length(); k++) {
            LayoutInflater itemCartInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull ItemRideProMultiStopAddressBinding bindingItem = ItemRideProMultiStopAddressBinding.inflate(itemCartInflater, layout, false);
            JSONObject obj = generalFunc.getJsonObject(jsonArray, k);

            bindingItem.squareImgView.setVisibility(View.GONE);
            bindingItem.dividerView.setVisibility(View.GONE);
            bindingItem.addressTxt.setText(generalFunc.getJsonValueStr("Value", obj));
            bindingItem.addressTxt.setTextColor(ContextCompat.getColor(context, R.color.text23Pro_Light));
            bindingItem.addressTxt.setPadding(0, 0, 0, (int) context.getResources().getDimension(R.dimen._6sdp));
            bindingItem.ivRound.setColorFilter(ContextCompat.getColor(context, R.color.textSub23Pro_gray), android.graphics.PorterDuff.Mode.SRC_IN);
            layout.addView(bindingItem.getRoot());
        }
    }

    public static void addSummaryRow(Context context, GeneralFunctions generalFunc, LinearLayout layout, String rName, String rValue, boolean isLast) {
        if (rName.equalsIgnoreCase("eDisplaySeperator")) {
            View convertView = new View(context);
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(context, 1));
            convertView.setLayoutParams(params);
            convertView.setBackgroundColor(context.getResources().getColor(R.color.view23ProBG));
            layout.addView(convertView);
        } else {
            final LayoutInflater inflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull ItemRideDetailsSummaryBinding bindingItem = ItemRideDetailsSummaryBinding.inflate(inflater, layout, false);

            bindingItem.summaryHTxt.setText(generalFunc.convertNumberWithRTL(rName));
            bindingItem.summaryVTxt.setText(generalFunc.convertNumberWithRTL(rValue));

            if (context instanceof RideBookSummary) {
                bindingItem.summaryVTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
            }
            if (isLast) {
                bindingItem.summaryHTxt.setTextColor(context.getResources().getColor(R.color.text23Pro_Dark));
                bindingItem.summaryHTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                bindingItem.summaryHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16);

                bindingItem.summaryVTxt.setTextColor(context.getResources().getColor(R.color.appThemeColor_1));
                bindingItem.summaryVTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                bindingItem.summaryVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16);
            }

            layout.addView(bindingItem.getRoot());
        }
    }

    @SuppressLint("SetTextI18n")
    public static BottomSheetDialog ratingBottomDialog(Context context, GeneralFunctions generalFunc, HashMap<String, String> dataHashMap, SimpleRatingBar mainRatingView) {
        BottomSheetDialog bottomSheetDialog = new BottomSheetDialog(context);
        DialogRideRatingBinding dialogBinding = DialogRideRatingBinding.inflate(LayoutInflater.from(context));
        bottomSheetDialog.setContentView(dialogBinding.getRoot());
        bottomSheetDialog.setCancelable(false);

        View bottomSheetView = bottomSheetDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(context.getResources().getColor(android.R.color.transparent));

        dialogBinding.titleHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RATINGS_REVIEW_RIDE_SHARE_TXT"));
        dialogBinding.nameTxt.setText(dataHashMap.get("toName"));
        dialogBinding.rideSharingRatingBar.setRating(mainRatingView.getRating());
        MyUtils.editBoxMultiLine(dialogBinding.EtReview);
        dialogBinding.EtReview.setHint(generalFunc.retrieveLangLBl("", "LBL_REVIEW_PLACEHOLDERL_RIDE_SHARE_TXT"));

        MButton sendRatingBtn = ((MaterialRippleLayout) dialogBinding.sendRatingBtn).getChildView();
        sendRatingBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RATE_RIDE_SHARE_TXT"));
        sendRatingBtn.setOnClickListener(v -> sendRating(context, generalFunc, bottomSheetDialog, dialogBinding, dataHashMap, mainRatingView));
        dialogBinding.closeImg.setOnClickListener(v -> bottomSheetDialog.dismiss());

        bottomSheetDialog.setOnDismissListener(dialog -> {
            // Instructions on bottomSheetDialog Dismiss
        });
        bottomSheetDialog.show();
        return bottomSheetDialog;
    }

    private static void sendRating(Context context, GeneralFunctions generalFunc, BottomSheetDialog bottomSheetDialog, DialogRideRatingBinding dialogBinding, HashMap<String, String> dataHashMap, SimpleRatingBar mainRatingView) {
        if (dialogBinding.rideSharingRatingBar.getRating() < 0.5) {
            generalFunc.showMessage(dialogBinding.closeImg, generalFunc.retrieveLangLBl("", "LBL_RATE_REQ_RIDE_SHARE_TXT"));
            return;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "RideShareRating");
        parameters.put("iBookingId", dataHashMap.get("iBookingId"));

        parameters.put("FromUserType", dataHashMap.get("FromUserType"));
        parameters.put("ToUserId", dataHashMap.get("ToUserId"));

        parameters.put("rating", "" + dialogBinding.rideSharingRatingBar.getRating());
        parameters.put("tMessage", Utils.getText(dialogBinding.EtReview));

        ApiHandler.execute(context, parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), i -> {
                        mainRatingView.setRating(dialogBinding.rideSharingRatingBar.getRating());
                        mainRatingView.setIndicator(true);
                        bottomSheetDialog.dismiss();

                        if (context instanceof RideBookDetails act) {
                            if (mainRatingView.isIndicator()) {
                                (new ActUtils(act)).setOkResult();
                            }
                        }
                    });
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError();
            }
        });
    }
}
