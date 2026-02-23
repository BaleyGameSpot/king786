package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemParkingListBinding;
import com.act.rentItem.adapter.PhotosAdapter;
import com.utils.MyUtils;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnClickListener onClickListener;
    PhotosAdapter photoAdapter;

    Context mcontext;


    public ParkingListAdapter(Context context, GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.onClickListener = listener;
        this.mcontext = context;
    }

    @NotNull
    @Override
    public DataViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int i) {
        return new DataViewHolder(ItemParkingListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        DataViewHolder viewHolder = (DataViewHolder) holder;
        HashMap<String, String> mapData = list.get(position);
        JSONArray photoArray = generalFunc.getJsonArray(mapData.get("ParkingSpaceImages"));

        viewHolder.binding.priceTxt.setText(mapData.get("tPrice"));
        viewHolder.binding.priceMsgTxt.setText(mapData.get("tPriceSubText"));
        viewHolder.binding.parkingAddress.setText(mapData.get("tAddress"));
        viewHolder.binding.ratingTxt.setText(mapData.get("vAvgRating"));
        viewHolder.binding.distanceTxt.setText(mapData.get("distance") + " " + mapData.get("DistanceSubText"));
        viewHolder.binding.noOfRating.setText(mapData.get("TotalRatings"));
        viewHolder.binding.btnBookNow.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW"));

        viewHolder.binding.btnBookNow.setOnClickListener(v -> onClickListener.onBookNowClick(position, mapData));
        viewHolder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));


        int bannerWidth = (int) (Utils.getScreenPixelWidth(mcontext) - mcontext.getResources().getDimensionPixelSize(R.dimen._25sdp));
        int bannerHeight = mcontext.getResources().getDimensionPixelSize(R.dimen._150sdp);
        ArrayList<HashMap<String, String>> photoData = new ArrayList<>();
        MyUtils.createArrayListJSONArray(generalFunc, photoData, photoArray);
        photoAdapter = new PhotosAdapter(photoData, bannerWidth, bannerHeight, (pos, finalImageUrl) -> {
            onClickListener.onItemClick(position, mapData);
//            (new ActUtils(mcontext)).openURL(finalImageUrl);
        }, false, true);
        viewHolder.binding.parkingPhotosViewPager.setAdapter(photoAdapter);
        if (photoData.size() > 1) {
            if (generalFunc.isRTLmode()) {
                viewHolder.binding.parkingPhotosViewPager.setCurrentItem(photoData.size() - 1);
            }
            viewHolder.binding.dotsIndicator.setViewPager(viewHolder.binding.parkingPhotosViewPager);
            viewHolder.binding.dotsIndicator.setVisibility(View.VISIBLE);
            viewHolder.binding.dotsArea.setVisibility(View.VISIBLE);
        } else {
            viewHolder.binding.dotsArea.setVisibility(View.GONE);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {

        private final ItemParkingListBinding binding;

        private DataViewHolder(@NonNull ItemParkingListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onBookNowClick(int position, HashMap<String, String> mapData);

        void onItemClick(int position, HashMap<String, String> mapData);
    }
}