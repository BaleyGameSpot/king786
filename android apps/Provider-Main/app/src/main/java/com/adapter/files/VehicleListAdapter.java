package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.appcompat.widget.AppCompatImageView;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 09-06-2017.
 */
public class VehicleListAdapter extends RecyclerView.Adapter<VehicleListAdapter.ViewHolder> {

    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list_item;
    Context mContext;
    OnItemClickList onItemClickList;

    public VehicleListAdapter(Context mContext, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_list_manage_vehicle_design, parent, false);

        ViewHolder viewHolder = new ViewHolder(view);
        return viewHolder;
    }

    @SuppressLint("UseCompatLoadingForColorStateLists")
    @Override
    public void onBindViewHolder(ViewHolder viewHolder, final int position) {

        HashMap<String, String> item = list_item.get(position);

        String eStatus = item.get("eStatus");
        if (eStatus.equalsIgnoreCase("Active")/* == "Active"*/) {
            viewHolder.statusTxtView.setText(item.get("LBL_ACTIVE"));
            viewHolder.statusHArea.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.green_light));
            viewHolder.statusTxtView.setTextColor(Color.parseColor("#25860b"));
        } else if (eStatus.equalsIgnoreCase("Inactive") /*== "Inactive"*/) {
            viewHolder.statusTxtView.setText(item.get("LBL_INACTIVE"));
            viewHolder.statusHArea.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.order_item_gray_color));
            viewHolder.statusTxtView.setTextColor(Color.parseColor("#757575"));
        } else if (eStatus.equalsIgnoreCase("Deleted")/* == "Deleted"*/) {
            viewHolder.statusTxtView.setText(item.get("LBL_DELETED"));
        } else {
            viewHolder.statusTxtView.setText(eStatus);
        }

        viewHolder.vNameTxtView.setText(item.get("vMake"));
        viewHolder.vOthInfoTxtView.setText(item.get("vLicencePlate"));


        viewHolder.docImgView.setOnClickListener(view -> {

            if (onItemClickList != null) {
                onItemClickList.onItemClick(position, 0);
            }
        });
        viewHolder.editImgView.setOnClickListener(view -> {

            if (onItemClickList != null) {
                onItemClickList.onItemClick(position, 1);
            }
        });
        viewHolder.deleteImgView.setOnClickListener(view -> {

            if (onItemClickList != null) {
                onItemClickList.onItemClick(position, 2);
            }
        });
    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position, int viewClickId);
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        public MTextView statusTxtView;
        public MTextView vNameTxtView;
        public MTextView vOthInfoTxtView;
        public AppCompatImageView docImgView;
        public AppCompatImageView editImgView;
        public AppCompatImageView deleteImgView;
        public LinearLayout statusHArea;

        public ViewHolder(View view) {
            super(view);

            statusTxtView = (MTextView) view.findViewById(R.id.statusTxtView);
            vNameTxtView = (MTextView) view.findViewById(R.id.vNameTxtView);
            vOthInfoTxtView = (MTextView) view.findViewById(R.id.vOthInfoTxtView);
            docImgView = (AppCompatImageView) view.findViewById(R.id.docImgView);
            editImgView = (AppCompatImageView) view.findViewById(R.id.editImgView);
            deleteImgView = (AppCompatImageView) view.findViewById(R.id.deleteImgView);
            statusHArea = (LinearLayout) view.findViewById(R.id.statusHArea);
        }
    }

}
