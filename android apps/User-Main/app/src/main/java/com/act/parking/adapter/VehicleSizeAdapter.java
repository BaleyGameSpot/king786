package com.act.parking.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 04-07-2016.
 */
public class VehicleSizeAdapter extends RecyclerView.Adapter<VehicleSizeAdapter.ViewHolder> {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private OnItemClickList onItemClickList;
    ArrayList<HashMap<String, String>> list_item;
    String selectedVehicleTypeId = "";

    private final int whiteColor;

    public VehicleSizeAdapter(Context mContext, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
        whiteColor = mContext.getResources().getColor(R.color.white);

    }


    @NonNull
    @Override
    public VehicleSizeAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view;
        view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_vehicle_size, parent, false);
        return new ViewHolder(view);
    }

    public void setSelectedVehicleTypeId(String selectedVehicleTypeId) {
        this.selectedVehicleTypeId = selectedVehicleTypeId;
    }


    @Override
    public void onBindViewHolder(@NotNull ViewHolder viewHolder, final int position) {
        setData(viewHolder, position);
    }

    private void setData(VehicleSizeAdapter.ViewHolder viewHolder, final int position) {
        HashMap<String, String> item = list_item.get(position);

        String vVehicleSize = item.get("size");
        String parkingId = item.get("iParkingVehicleSizeId");
        String subTxt = item.get("sizeEg");

        viewHolder.carSize.setText(vVehicleSize);
        viewHolder.carSizeEg.setText(subTxt);

        if (selectedVehicleTypeId.equalsIgnoreCase(parkingId)) {
            viewHolder.contentArea.setBackground(mContext.getResources().getDrawable(R.drawable.cab_select_item_background_shadow));
        } else {
            viewHolder.contentArea.setBackground(null);
        }


        viewHolder.contentArea.setOnClickListener(view -> {

            if (onItemClickList != null) {
                onItemClickList.onItemClick(position);
            }
        });
        String imageUrl = item.get("vImage");
        new LoadImage.builder(LoadImage.bind(imageUrl), viewHolder.carTypeImageView).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {
//                viewHolder.loaderView.setVisibility(View.GONE);
            }

            @Override
            public void onError() {
//                viewHolder.loaderView.setVisibility(View.VISIBLE);
            }
        }).build();

    }



    /*private void loadImage(final VehicleSizeAdapter.ViewHolder holder, String imageUrl) {

        new LoadImage.builder(LoadImage.bind(imageUrl), holder.carTypeImgView).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {
                holder.loaderView.setVisibility(View.GONE);
            }

            @Override
            public void onError() {
                holder.loaderView.setVisibility(View.VISIBLE);
            }
        }).build();
        new LoadImage.builder(LoadImage.bind(imageUrl), holder.carTypeImgViewselcted).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {
                holder.loaderView.setVisibility(View.GONE);
            }

            @Override
            public void onError() {
                holder.loaderView.setVisibility(View.VISIBLE);
            }
        }).build();


    }*/

    @Override
    public int getItemCount() {
        return list_item.size();
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position);
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {


        RelativeLayout contentArea;
        MTextView carSize;
        MTextView carSizeEg;
        AppCompatImageView carTypeImageView;

        private ViewHolder(View view) {
            super(view);

            contentArea = view.findViewById(R.id.contentArea);
            carSize = view.findViewById(R.id.carSizeTitle);
            carSizeEg = view.findViewById(R.id.carTypeDesc);
            carTypeImageView = view.findViewById(R.id.carTypeImgView);
        }
    }
}