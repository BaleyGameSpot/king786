package com.act.rideSharingPro.adapter;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.act.FareBreakDownActivity;
import com.act.rideSharingPro.RideSharingUtils;
import com.general.call.CommunicationManager;
import com.general.call.DefaultCommunicationHandler;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemRideMyPassengerListBinding;
import com.model.ServiceModule;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideMyPassengerAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener listener;
    private final String LBL_VIEW_REASON, LBL_DECLINE_TXT, LBL_ACCEPT_TXT, callingMethod;
    private BottomSheetDialog bottomSheetDialog;

    public RideMyPassengerAdapter(GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> list, OnItemClickListener mItemClickListener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.listener = mItemClickListener;
        this.LBL_VIEW_REASON = generalFunc.retrieveLangLBl("", "LBL_VIEW_REASON");
        this.LBL_DECLINE_TXT = generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT");
        this.LBL_ACCEPT_TXT = generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT");
        this.callingMethod = generalFunc.getJsonValue("CALLING_METHOD_RIDE_SHARE", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemRideMyPassengerListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        if (holder instanceof ViewHolder listHolder) {
            HashMap<String, String> mapData = list.get(position);

            listHolder.binding.passengerNameTxt.setText(mapData.get("rider_Name"));

            String tLocation = mapData.get("tLocation");
            if (ServiceModule.EnableRideSharingPro && Utils.checkText(tLocation)) {
                listHolder.binding.locationTxt.setVisibility(View.VISIBLE);
                listHolder.binding.locationTxt.setText(tLocation);
            } else {
                listHolder.binding.locationTxt.setVisibility(View.GONE);
            }

            String statusMessage = mapData.get("statusMessage");
            if (Utils.checkText(statusMessage)) {
                listHolder.binding.statusTxt.setVisibility(View.VISIBLE);
                listHolder.binding.statusTxt.setText(statusMessage);
            } else {
                listHolder.binding.statusTxt.setVisibility(View.GONE);
            }

            if (mapData.containsKey("BookedSeatsTxt")) {
                listHolder.binding.availableSeatsTxt.setText(mapData.get("BookedSeatsTxt"));
            }
            if (Objects.requireNonNull(mapData.get("eShowCallImg")).equalsIgnoreCase("Yes")) {

                listHolder.binding.callArea.setVisibility(View.VISIBLE);
                listHolder.binding.callArea.setOnClickListener(view -> {
                    MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                            .setToMemberId(mapData.get("rider_iUserId"))
                            .setToMemberName(mapData.get("rider_Name"))
                            .setPhoneNumber(mapData.get("rider_Phone"))
                            .setToMemberType(Utils.CALLTOPASSENGER)
                            .setToMemberImage(mapData.get("rider_ProfileImg"))
                            .setMedia(CommunicationManager.MEDIA_TYPE)
                            .build();
                    if (callingMethod.equalsIgnoreCase("VOIP")) {
                        CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.PHONE_CALL);
                    } else if (callingMethod.equalsIgnoreCase("VIDEOCALL")) {
                        CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.VIDEO_CALL);
                    } else if (callingMethod.equalsIgnoreCase("VOIP-VIDEOCALL")) {
                        CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.BOTH_CALL);
                    } else if (!Utils.checkText(callingMethod) ||
                            callingMethod.equalsIgnoreCase("NORMAL")) {
                        DefaultCommunicationHandler.getInstance().executeAction(MyApp.getInstance().getCurrentAct(), CommunicationManager.TYPE.PHONE_CALL, mDataProvider);
                    }
                });
            } else {
                listHolder.binding.callArea.setVisibility(View.GONE);
            }
            String riderImage = mapData.get("rider_ProfileImg");
            if (!Utils.checkText(riderImage)) {
                riderImage = "Temp";
            }
            new LoadImage.builder(LoadImage.bind(riderImage), listHolder.binding.rideDriverImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

            MButton viewReasonBtn = ((MaterialRippleLayout) listHolder.binding.viewReasonBtn).getChildView();
            MButton declineBtn = ((MaterialRippleLayout) listHolder.binding.declineBtn).getChildView();
            MButton acceptBtn = ((MaterialRippleLayout) listHolder.binding.acceptBtn).getChildView();

            viewReasonBtn.setText(LBL_VIEW_REASON);
            declineBtn.setText(LBL_DECLINE_TXT);
            acceptBtn.setText(LBL_ACCEPT_TXT);

            listHolder.binding.viewReasonBtnArea.setVisibility(View.GONE);
            listHolder.binding.declineBtnArea.setVisibility(View.GONE);
            listHolder.binding.acceptBtnArea.setVisibility(View.GONE);

            String eStatus = mapData.get("eStatus");
            if (eStatus != null) {
                if (eStatus.equalsIgnoreCase("Pending")) {

                    listHolder.binding.declineBtnArea.setVisibility(View.VISIBLE);
                    listHolder.binding.acceptBtnArea.setVisibility(View.VISIBLE);
                    declineBtn.setOnClickListener(v -> listener.onDeclineClick(mapData, position));
                    acceptBtn.setOnClickListener(v -> listener.onAcceptClick(mapData, position));

                }
            }

            if (mapData.containsKey("DeclineReason") && Utils.checkText(mapData.get("DeclineReason"))) {
                listHolder.binding.viewReasonBtnArea.setVisibility(View.VISIBLE);
                viewReasonBtn.setOnClickListener(v -> listener.onViewReasonClick(mapData));
            }

            listHolder.binding.paymentModeHTxt.setText(mapData.get("PaymentModeTitle") + ": ");
            listHolder.binding.paymentModeVTxt.setText(mapData.get("PaymentModeLabel"));

            String priceBreakdown = mapData.get("PriceBreakdown");
            if (Utils.checkText(priceBreakdown)) {
                listHolder.binding.totalPriceTxt.setVisibility(View.VISIBLE);
                listHolder.binding.totalPriceTxt.setText(mapData.get("TotalFare"));
                listHolder.binding.totalPriceTxt.setOnClickListener(v -> {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isData", true);
                    bn.putString("fareData", priceBreakdown);
                    bn.putString("PriceBreakdownTitle", mapData.get("PriceBreakdownTitle"));
                    new ActUtils(listHolder.itemView.getContext()).startActWithData(FareBreakDownActivity.class, bn);
                });
            } else {
                listHolder.binding.totalPriceTxt.setVisibility(View.GONE);
            }
            String PaymentLabel = mapData.get("PaymentLabel");
            if (Utils.checkText(PaymentLabel)) {
                listHolder.binding.paymentNoteTxt.setVisibility(View.VISIBLE);
                listHolder.binding.paymentNoteTxt.setText(PaymentLabel);
            } else {
                listHolder.binding.paymentNoteTxt.setVisibility(View.GONE);
            }

            if (ServiceModule.EnableRideSharingPro && mapData.containsKey("IS_RATING_SHOW") && Objects.requireNonNull(mapData.get("IS_RATING_SHOW")).equalsIgnoreCase("Yes")) {
                listHolder.binding.rideSharingRatingBar.setVisibility(View.VISIBLE);

                String setRating = mapData.get("rating");
                if (Utils.checkText(setRating)) {
                    listHolder.binding.rideSharingRatingBar.setRating(GeneralFunctions.parseFloatValue(0, setRating));
                    listHolder.binding.rideSharingRatingBar.setIndicator(true);
                } else {
                    listHolder.binding.rideSharingRatingBar.setOnRatingBarChangeListener((simpleRatingBar, v, b) -> {
                        if (bottomSheetDialog != null && bottomSheetDialog.isShowing()) {
                            return;
                        }
                        HashMap<String, String> dataHashMap = new HashMap<>();
                        dataHashMap.put("toName", mapData.get("rider_Name"));
                        dataHashMap.put("iBookingId", mapData.get("iBookingId"));
                        dataHashMap.put("FromUserType", "driver");
                        dataHashMap.put("ToUserId", mapData.get("rider_iUserId"));
                        bottomSheetDialog = RideSharingUtils.ratingBottomDialog(listHolder.itemView.getContext(), generalFunc, dataHashMap, listHolder.binding.rideSharingRatingBar);
                    });
                }
            } else {
                listHolder.binding.rideSharingRatingBar.setVisibility(View.GONE);
            }
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemRideMyPassengerListBinding binding;

        private ViewHolder(ItemRideMyPassengerListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onViewReasonClick(HashMap<String, String> hashMap);

        void onDeclineClick(HashMap<String, String> hashMap, int position);

        void onAcceptClick(HashMap<String, String> hashMap, int position);
    }
}