package com.adapter.files;

import android.annotation.SuppressLint;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemAddressDesignBinding;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class AddressListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final ItemClickListener clickListener;
    private final ArrayList<HashMap<String, String>> deliveryList;
    private int iSelectedPosition;

    public AddressListAdapter(ArrayList<HashMap<String, String>> deliveryList, ItemClickListener itemClickListener) {
        this.deliveryList = deliveryList;
        this.clickListener = itemClickListener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemAddressDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, @SuppressLint("RecyclerView") final int position) {

        final HashMap<String, String> item = deliveryList.get(position);
        final ViewHolder viewHolder = (ViewHolder) holder;

        String vAddressType = item.get("vAddressType");

        if (vAddressType != null && !vAddressType.equals("")) {
            viewHolder.binding.titleTxt.setText(vAddressType + "\n" + item.get("vBuildingNo") + ", " + item.get("vLandmark"));
        } else {
            viewHolder.binding.titleTxt.setText(item.get("vBuildingNo") + ", " + item.get("vLandmark"));
        }
        viewHolder.binding.tAddressTxt.setText(item.get("vServiceAddress"));

        viewHolder.binding.radioImg.setOnClickListener(view -> viewHolder.binding.deliveryAddressArea.performClick());

        if (Objects.requireNonNull(item.get("isSelected")).equalsIgnoreCase("false")) {
            viewHolder.binding.radioImg.setImageResource(R.drawable.ic_non_selected);
        } else {
            viewHolder.binding.radioImg.setImageResource(R.drawable.ic_selected);
            iSelectedPosition = position;
        }

        String eLocationAvailable = item.get("eLocationAvailable");
        viewHolder.binding.deliveryAddressArea.setOnClickListener(view -> {
            if (eLocationAvailable != null && eLocationAvailable.equalsIgnoreCase("No")) {
                return;
            }

            viewHolder.binding.radioImg.setImageResource(R.drawable.ic_selected);
            if (clickListener != null) {

                deliveryList.get(iSelectedPosition).put("isSelected", "false");
                notifyItemChanged(iSelectedPosition);
                iSelectedPosition = position;

                new android.os.Handler().postDelayed(() -> clickListener.setOnClick(position), 150);
            }
        });
        viewHolder.binding.imgDelete.setOnClickListener(view -> {
            if (clickListener != null) {
                clickListener.setOnDeleteClick(position);
            }
        });

        if (eLocationAvailable != null && eLocationAvailable.equalsIgnoreCase("Yes")) {
            viewHolder.binding.tAddressTxt.setTextColor(viewHolder.itemView.getContext().getResources().getColor(R.color.black));
            viewHolder.binding.radioImg.setEnabled(true);
            viewHolder.binding.radioImg.setFocusable(true);
            viewHolder.binding.radioImg.setFocusableInTouchMode(true);
            viewHolder.binding.addressNotAvailTxtView.setVisibility(View.GONE);

        } else if (eLocationAvailable != null && eLocationAvailable.equalsIgnoreCase("No")) {
            int color = Color.parseColor("#808080");
            viewHolder.binding.tAddressTxt.setTextColor(color);
            viewHolder.binding.radioImg.setColorFilter(color);
            viewHolder.binding.radioImg.setEnabled(false);
            viewHolder.binding.radioImg.setFocusable(false);
            viewHolder.binding.radioImg.setFocusableInTouchMode(false);
            viewHolder.binding.addressNotAvailTxtView.setVisibility(View.VISIBLE);
            viewHolder.binding.addressNotAvailTxtView.setText(item.get("LBL_SERVICE_NOT_AVAIL_ADDRESS_RESTRICT"));
        }
    }

    @Override
    public int getItemCount() {
        return deliveryList.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemAddressDesignBinding binding;

        private ViewHolder(ItemAddressDesignBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface ItemClickListener {
        void setOnClick(int position);

        void setOnDeleteClick(int position);
    }
}
