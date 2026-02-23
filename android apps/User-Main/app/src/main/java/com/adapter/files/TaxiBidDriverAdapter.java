package com.adapter.files;

import android.annotation.SuppressLint;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemTaxiBidDriversBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class TaxiBidDriverAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private JSONArray mDriverArr;
    private final OnClickListener listener;
    private final String LBL_YOUR_FARE_TAXI_BID_TXT, LBL_ACCEPT_TAXI_BID_TEXT, LBL_DECLINE_TXT;
    private final int maxProgressValue;

    public TaxiBidDriverAdapter(@NonNull GeneralFunctions generalFunc, @NonNull JSONArray array, @NonNull OnClickListener listener) {
        this.generalFunc = generalFunc;
        this.mDriverArr = array;
        this.listener = listener;

        this.LBL_YOUR_FARE_TAXI_BID_TXT = generalFunc.retrieveLangLBl("", "LBL_YOUR_FARE_TAXI_BID_TXT");
        this.LBL_ACCEPT_TAXI_BID_TEXT = generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TAXI_BID_TEXT");
        this.LBL_DECLINE_TXT = generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT");

        this.maxProgressValue = GeneralFunctions.parseIntegerValue(30, generalFunc.retrieveValue("DRIVER_QUOTATION_ACCEPT_TIME_BID_TAXI"));
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemTaxiBidDriversBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        ViewHolder bHolder = (ViewHolder) holder;

        JSONObject itemObject = generalFunc.getJsonObject(mDriverArr, position);

        String driverImage = generalFunc.getJsonValueStr("driverImage", itemObject);
        if (!Utils.checkText(driverImage)) {
            driverImage = "Temp";
        }
        new LoadImage.builder(LoadImage.bind(driverImage), bHolder.binding.driverImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        bHolder.binding.driverNameTxt.setText(generalFunc.getJsonValueStr("driverName", itemObject));
        bHolder.binding.driverVehicleTxt.setText(generalFunc.getJsonValueStr("driverVehicle", itemObject));

        String dRating = generalFunc.getJsonValueStr("driverAvgRating", itemObject);
        if (Utils.checkText(dRating)) {
            bHolder.binding.driverRatingView.setVisibility(View.VISIBLE);
            bHolder.binding.driverRatingVTxt.setText(dRating);
        } else {
            bHolder.binding.driverRatingView.setVisibility(View.GONE);
        }

        bHolder.binding.timeToReachTxt.setText(generalFunc.getJsonValueStr("TimeToReach", itemObject));
        bHolder.binding.durationToReachTxt.setText(generalFunc.getJsonValueStr("DurationToReach", itemObject));

        //
        bHolder.binding.driverBidFareTxt.setText(generalFunc.getJsonValueStr("OfferFare", itemObject));
        if (generalFunc.getJsonValueStr("isSameFare", itemObject).equalsIgnoreCase("Yes")) {
            bHolder.binding.taxiBidStatusView.setVisibility(View.VISIBLE);
            bHolder.binding.taxiBidStatusTxt.setText(LBL_YOUR_FARE_TAXI_BID_TXT);
        } else {
            bHolder.binding.taxiBidStatusView.setVisibility(View.GONE);
        }

        /////-------------------------------------------------------------------------------
        bHolder.binding.timerProgressBar.setMax(maxProgressValue);
        int driverTimer = GeneralFunctions.parseIntegerValue(30, generalFunc.getJsonValueStr("driverTimer", itemObject));
        bHolder.binding.timerProgressBar.setProgress(driverTimer);

        ///////////////////////////////////////////////////////////////-----------------------------
        bHolder.binding.declineBtnTxt.setText(LBL_DECLINE_TXT);
        bHolder.binding.declineBtnArea.setOnClickListener(v -> {
            bHolder.binding.declineBtnArea.setEnabled(false);
            listener.onDeclineClick(position, itemObject);
            new Handler(Looper.myLooper()).postDelayed(() -> bHolder.binding.declineBtnArea.setEnabled(true), 777);
        });
        bHolder.binding.acceptBtnTxt.setText(LBL_ACCEPT_TAXI_BID_TEXT);
        bHolder.binding.acceptBtnArea.setOnClickListener(v -> {
            bHolder.binding.acceptBtnArea.setEnabled(false);
            listener.onAcceptClick(itemObject);
            new Handler(Looper.myLooper()).postDelayed(() -> bHolder.binding.acceptBtnArea.setEnabled(true), 777);
        });

    }

    @Override
    public int getItemCount() {
        if (mDriverArr == null) {
            return 0;
        }
        return mDriverArr.length();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray jsonArray) {
        this.mDriverArr = jsonArray;
        notifyDataSetChanged();
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemTaxiBidDriversBinding binding;

        private ViewHolder(ItemTaxiBidDriversBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onAcceptClick(@NonNull JSONObject itemObject);

        void onDeclineClick(int position, @NonNull JSONObject itemObject);
    }
}