package com.adapter.files;

import android.content.Context;

import androidx.recyclerview.widget.RecyclerView;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import com.buddyverse.main.R;
import com.general.files.GeneralFunctions;
import com.general.files.showTermsDialog;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 02-03-2017.
 */
public class DeliveryBannerAdapter extends RecyclerView.Adapter<DeliveryBannerAdapter.ViewHolder> {

    private ArrayList<HashMap<String, String>> list_item;
    private Context context;
    OnBannerItemClickList onItemClickList;
    GeneralFunctions generalFunctions;

    public DeliveryBannerAdapter(Context context, ArrayList<HashMap<String, String>> list_item) {
        this.context = context;
        this.list_item = list_item;
        generalFunctions = new GeneralFunctions(context);
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_delivery_banner_design, parent, false);
        DeliveryBannerAdapter.ViewHolder viewHolder = new DeliveryBannerAdapter.ViewHolder(view);
        return viewHolder;
    }

    @Override
    public void onBindViewHolder(ViewHolder holder, int position) {
        HashMap<String, String> mapData = list_item.get(position);

        String vBannerImage = mapData.get("vImage");
        String eShowTermsStr = mapData.get("eShowTerms");
        boolean eShowTerms = Utils.checkText(eShowTermsStr) && eShowTermsStr.equalsIgnoreCase("yes") ? true : false;


        new LoadImage.builder(LoadImage.bind(vBannerImage), holder.bannerImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

        holder.serviceNameTxt.setText(mapData.get("vCategory"));


        holder.bookNowTxt.setText(mapData.get("tBannerButtonText"));
        holder.bookNowTxt.setVisibility(View.GONE);

        holder.bannerAreaContainerView.setOnClickListener(v -> {
            if (onItemClickList != null) {

                if (eShowTerms && !CommonUtilities.ageRestrictServices.contains("1")) {
                    new showTermsDialog(getActContext(), generalFunctions, position, mapData.get("vCategory"), click, new showTermsDialog.OnClickList() {
                        @Override
                        public void onClick() {
                            onItemClickList.onBannerItemClick(position);
                        }
                    });

                } else {
                    onItemClickList.onBannerItemClick(position);
                }

            }
        });

        if (generalFunctions.isRTLmode()) {
            if (holder.bookNowImg != null) {
                holder.bookNowImg.setScaleX(-1);
            }
        }

    }


    boolean click = false;

    private Context getActContext() {

        return context;
    }


    public void setOnItemClickList(OnBannerItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnBannerItemClickList {
        void onBannerItemClick(int position);
    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }


    public class ViewHolder extends RecyclerView.ViewHolder {


        public View contentArea;
        public View bannerAreaContainerView;
        public ImageView bannerImgView;
        public MTextView bookNowTxt, serviceNameTxt;
        ImageView bookNowImg;

        public ViewHolder(View view) {
            super(view);

            bannerImgView = (ImageView) view.findViewById(R.id.bannerImgView);
            bookNowTxt = (MTextView) view.findViewById(R.id.bookNowTxt);
            serviceNameTxt = (MTextView) view.findViewById(R.id.serviceNameTxt);
            bannerAreaContainerView = view.findViewById(R.id.bannerAreaContainerView);
            bookNowImg = view.findViewById(R.id.bookNowImg);
        }
    }


}