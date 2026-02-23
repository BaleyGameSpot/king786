package com.act.giftcard.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemGiftcardBinding;
import com.utils.LoadImage;
import com.utils.PreLoadImages;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class GiftCardImagePagerAdapter extends RecyclerView.Adapter<GiftCardImagePagerAdapter.ViewHolder> {

    private final ArrayList<HashMap<String, String>> list;
    private final SendGiftCardOnClickListener SendGiftCardOnClickListener;
    private final int width, height;

    public GiftCardImagePagerAdapter(Context mContext, ArrayList<HashMap<String, String>> list, SendGiftCardOnClickListener SendGiftCardOnClickListener) {
        this.list = list;
        this.SendGiftCardOnClickListener = SendGiftCardOnClickListener;

        this.width = PreLoadImages.getGiftCardImagesWidth(mContext, true);
        this.height = PreLoadImages.getGiftCardImagesWidth(mContext, false);
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int i) {
        return new ViewHolder(ItemGiftcardBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {

        CardView.LayoutParams bannerLayoutParams = (CardView.LayoutParams) holder.binding.SendGiftCardImg.getLayoutParams();
        bannerLayoutParams.width = width;
        bannerLayoutParams.height = height;
        holder.binding.SendGiftCardImg.setLayoutParams(bannerLayoutParams);

        String imageUrl = list.get(position).get("vImage");
        if (!Utils.checkText(imageUrl)) {
            imageUrl = "Temp";
        }
        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(holder.itemView.getContext(), imageUrl, width, height)), holder.binding.SendGiftCardImg).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();

        holder.binding.mainArea.setBackground(ContextCompat.getDrawable(holder.itemView.getContext(), R.drawable.bg_services_unselector));
        if (list.get(position).containsKey("isCheck")) {
            if (Objects.requireNonNull(list.get(position).get("isCheck")).equalsIgnoreCase("Yes")) {
                holder.binding.mainArea.setBackground(ContextCompat.getDrawable(holder.itemView.getContext(), R.drawable.bg_services_selector));
            }
        } else if (position == 0) {
            holder.binding.mainArea.setBackground(ContextCompat.getDrawable(holder.itemView.getContext(), R.drawable.bg_services_selector));
        }
        holder.binding.mainArea.setOnClickListener(view -> SendGiftCardOnClickListener.setOnSendGiftCardClick(position));
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemGiftcardBinding binding;

        private ViewHolder(ItemGiftcardBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface SendGiftCardOnClickListener {
        void setOnSendGiftCardClick(int position);
    }
}