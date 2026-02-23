package com.act.rideSharingPro.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.databinding.CarpoolRidersDetailBinding;

import org.json.JSONArray;
import org.json.JSONObject;

public class RideShareRiderDetailsAdapter extends RecyclerView.Adapter<RideShareRiderDetailsAdapter.ViewHolder> {
    private final GeneralFunctions generalFunc;
    private final JSONArray jsonArray;

    public RideShareRiderDetailsAdapter(GeneralFunctions generalFunc, JSONArray jsonArray) {
        this.generalFunc = generalFunc;
        this.jsonArray = jsonArray;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(CarpoolRidersDetailBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(final ViewHolder viewHolder, final int position) {
        final JSONObject object = generalFunc.getJsonObject(jsonArray, position);

        viewHolder.binding.arrowArea.setOnClickListener(view -> {
            if (viewHolder.binding.recipientDetailArea.getVisibility() == View.VISIBLE) {
                viewHolder.binding.recipientDetailArea.setVisibility(View.GONE);
                viewHolder.binding.imgArrow.setRotation(0);
            } else {
                viewHolder.binding.recipientDetailArea.setVisibility(View.VISIBLE);
                viewHolder.binding.imgArrow.setRotation(-180);
            }
        });

        viewHolder.binding.riderSeqNumber.setText("" + (position + 1));
        viewHolder.binding.recipientNoTxt.setText(generalFunc.getJsonValueStr("rider_Name", object));
        viewHolder.binding.nextDeliveryLocTxt.setText(generalFunc.getJsonValueStr("RiderLabel", object));
        viewHolder.binding.totalSheetsTxt.setText(generalFunc.getJsonValueStr("iBookedSeatsLabel", object) + " : " + generalFunc.getJsonValueStr("iBookedSeats", object));

        if (jsonArray.length() == 1) {
            viewHolder.binding.mainLineTop.setVisibility(View.GONE);
            viewHolder.binding.arrowArea.performClick();
            viewHolder.binding.arrowArea.setVisibility(View.GONE);
        } else {
            if (position == 0) {
                viewHolder.binding.mainLineTop.setVisibility(View.GONE);
            } else {
                viewHolder.binding.mainLineTop.setVisibility(View.VISIBLE);
            }
        }

        viewHolder.binding.ricipientSignTxt.setVisibility(View.INVISIBLE);

        viewHolder.binding.paymentModeHtxt.setText(generalFunc.getJsonValueStr("PaymentModeTitle", object));
        viewHolder.binding.paymentModeVtxt.setText(generalFunc.getJsonValueStr("PaymentModeLabel", object));
        viewHolder.binding.AmountHtxt.setText(generalFunc.getJsonValueStr("TotalFareLabel", object));
        viewHolder.binding.AmountVtxt.setText(generalFunc.getJsonValueStr("TotalFare", object));


        if (position == 0) {
            viewHolder.binding.centerLayout.setPadding(0, 0, 0, 50);
        } else if (position == jsonArray.length() - 1) {
            viewHolder.binding.centerLayout.setPadding(0, 0, 0, 0);
        } else {
            viewHolder.binding.centerLayout.setPadding(0, 0, 0, 50);
        }
    }

    @Override
    public int getItemCount() {
        return jsonArray.length();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final CarpoolRidersDetailBinding binding;

        private ViewHolder(CarpoolRidersDetailBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}