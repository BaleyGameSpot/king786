package com.adapter.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.general.files.GeneralFunctions;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class UberXSearchCategoryAdapter extends RecyclerView.Adapter<UberXSearchCategoryAdapter.ViewHolder> {

    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list_item;
    Context mContext;
    OnItemClickList onItemClickList;

    String CAT_TYPE_MODE = "0";

    public UberXSearchCategoryAdapter(Context mContext, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_search_cat, parent, false);
        ViewHolder viewHolder = new ViewHolder(view);
        return viewHolder;
    }

    public void setCategoryMode(String CAT_TYPE_MODE) {
        this.CAT_TYPE_MODE = CAT_TYPE_MODE;
    }

    @Override
    public void onBindViewHolder(ViewHolder viewHolder, final int position) {

        HashMap<String, String> item = list_item.get(position);
        String vLogo = item.get("vLogo");
        String vCategory = item.get("vCategory");

        viewHolder.uberXServiceNameTxt.setText(vCategory);

        new LoadImage.builder(LoadImage.bind(vLogo.equals("") ? CommonUtilities.SERVER : vLogo), viewHolder.catImgView).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {

            }

            @Override
            public void onError() {
                if (!vLogo.contains("http") && !vLogo.equals("")) {
                    new LoadImage.builder(LoadImage.bind(GeneralFunctions.parseIntegerValue(0, vLogo)), viewHolder.catImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

                }

            }
        }).setPlaceholderImagePath(R.mipmap.ic_no_icon).setErrorImagePath(R.mipmap.ic_no_icon).build();


        viewHolder.contentArea.setOnClickListener(view -> {
            if (onItemClickList != null) {
                onItemClickList.onItemClick(position);
            }
        });

        if (position == list_item.size() - 1) {
            viewHolder.viewDecoration.setVisibility(View.GONE);
        } else {
            viewHolder.viewDecoration.setVisibility(View.VISIBLE);
        }

    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position);

        void onMultiItem(String id, boolean b);
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        public MTextView uberXServiceNameTxt, uberXServiceDetailTxt;
        public View contentArea, viewDecoration;
        public ImageView catImgView;


        public ViewHolder(View view) {
            super(view);

            uberXServiceNameTxt = (MTextView) view.findViewById(R.id.uberXServiceNameTxt);
            uberXServiceDetailTxt = (MTextView) view.findViewById(R.id.uberXServiceDetailTxt);

            contentArea = view.findViewById(R.id.contentArea);
            catImgView = (ImageView) view.findViewById(R.id.catImgView);
            viewDecoration = view.findViewById(R.id.viewDecoration);
        }
    }


}
