package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemVehicleListParkingBinding;
import com.utils.LoadImageGlide;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class VehicleListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnClickListener onClickListener;
    Context mcontext;
    private boolean isProfile;

    public VehicleListAdapter(Context context, GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener, boolean isProfile) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.onClickListener = listener;
        this.mcontext = context;
        this.isProfile = isProfile;
    }

    @NotNull
    @Override
    public DataViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int i) {
        return new DataViewHolder(ItemVehicleListParkingBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        DataViewHolder viewHolder = (DataViewHolder) holder;
        HashMap<String, String> mapData = list.get(position);

        viewHolder.binding.carName.setText(mapData.get("VehicleMakeModel"));
        viewHolder.binding.carSize.setText(mapData.get("vCarSize"));
        viewHolder.binding.carNumber.setText(mapData.get("vCarNumberPlate"));
        viewHolder.binding.disAllowNote.setText(mapData.get("Note"));
        float radius = mcontext.getResources().getDimension(R.dimen._7sdp);
        if (!mapData.get("isAllowSelection").equalsIgnoreCase("yes")) {
            viewHolder.binding.disAllowNote.setVisibility(View.VISIBLE);

        } else {
            viewHolder.binding.disAllowNote.setVisibility(View.GONE);
        }

        if (isProfile) {
            viewHolder.binding.carImg.setShapeAppearanceModel(viewHolder.binding.carImg.getShapeAppearanceModel().toBuilder().setTopLeftCorner(CornerFamily.ROUNDED, radius).setTopRightCorner(CornerFamily.ROUNDED, radius).build());
            new LoadImageGlide.builder(mcontext, LoadImageGlide.bind(mapData.get("vImage")), viewHolder.binding.carImg).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            viewHolder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));

//            viewHolder.binding.btnEdit.setText(generalFunc.retrieveLangLBl("", "LBL_EDIT"));
//            viewHolder.binding.btnDelete.setText(generalFunc.retrieveLangLBl("", "LBL_DELETE"));
        } else {
            viewHolder.binding.carImg.setShapeAppearanceModel(viewHolder.binding.carImg.getShapeAppearanceModel().toBuilder().setTopLeftCorner(CornerFamily.ROUNDED, radius).setBottomLeftCorner(CornerFamily.ROUNDED, radius).build());
            new LoadImageGlide.builder(mcontext, LoadImageGlide.bind(mapData.get("vImage")), viewHolder.binding.carImg).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            if (mapData.get("isSelected").equalsIgnoreCase("yes")) {
                viewHolder.binding.itemArea.setBackgroundTintList(ColorStateList.valueOf(mcontext.getResources().getColor(R.color.appThemeColor_1)));
                viewHolder.binding.selectedImg.setVisibility(View.VISIBLE);
                viewHolder.binding.bgSelectedImgView.setVisibility(View.VISIBLE);
            } else {
                viewHolder.binding.itemArea.setBackgroundTintList(ColorStateList.valueOf(mcontext.getResources().getColor(R.color.border_gray_vehicle)));
                viewHolder.binding.selectedImg.setVisibility(View.GONE);
                viewHolder.binding.bgSelectedImgView.setVisibility(View.GONE);
            }
            viewHolder.binding.carImg.getLayoutParams().height = viewHolder.binding.llInfo.getHeight();
            viewHolder.binding.carImg.requestLayout();

        }
        if (!mapData.get("isAllowSelection").equalsIgnoreCase("yes")) {
            viewHolder.binding.disAllowNote.setVisibility(View.VISIBLE);
        } else {
            viewHolder.binding.disAllowNote.setVisibility(View.GONE);
            viewHolder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));
        }
        viewHolder.binding.btnDelete.setOnClickListener(v -> onClickListener.onDeleteClick(position, mapData));
        viewHolder.binding.btnEdit.setOnClickListener(v -> onClickListener.onEditClick(position, mapData));
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {
        private final ItemVehicleListParkingBinding binding;

        private DataViewHolder(@NonNull ItemVehicleListParkingBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {

        void onDeleteClick(int position, HashMap<String, String> mapData);

        void onItemClick(int position, HashMap<String, String> mapData);

        void onEditClick(int position, HashMap<String, String> mapData);
    }
}