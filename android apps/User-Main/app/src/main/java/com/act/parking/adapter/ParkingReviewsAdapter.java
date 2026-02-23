package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemParkingReviewBinding;
import com.utils.LoadImageGlide;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingReviewsAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnClickListener onClickListener;
    Context mcontext;

    public ParkingReviewsAdapter(Context context, GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.onClickListener = listener;
        this.mcontext = context;
    }

    @NotNull
    @Override
    public DataViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int i) {
        return new DataViewHolder(ItemParkingReviewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        DataViewHolder viewHolder = (DataViewHolder) holder;
        HashMap<String, String> mapData = list.get(position);

        viewHolder.binding.reviewTxt.setText(mapData.get("Message"));
        viewHolder.binding.rName.setText(mapData.get("vName"));
        viewHolder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));
        viewHolder.binding.ratingBar.setRating(GeneralFunctions.parseFloatValue(0, mapData.get("Rating")));

        new LoadImageGlide.builder(mcontext, LoadImageGlide.bind(mapData.get("vImage")), viewHolder.binding.rImage).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();


    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {

        private final ItemParkingReviewBinding binding;

        private DataViewHolder(@NonNull ItemParkingReviewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onItemClick(int position, HashMap<String, String> mapData);
    }
}