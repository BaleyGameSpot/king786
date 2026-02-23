package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemTransactionTypeBinding;
import com.model.TransactionTypesModel;
import com.utils.LoadImageGlide;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;

public class RecentTransactionsTypeRecycleAdapter extends RecyclerView.Adapter<RecentTransactionsTypeRecycleAdapter.ViewHolder> {

    private final GeneralFunctions generalFunctions;
    private final ArrayList<TransactionTypesModel> list_item = new ArrayList<>();
    private OnTypeClickList onItemClickList;

    int sWidth;

    public RecentTransactionsTypeRecycleAdapter(Context context, GeneralFunctions generalFunc, ArrayList<TransactionTypesModel> list_item) {
        this.generalFunctions = generalFunc;
        this.list_item.addAll(list_item);

        this.sWidth = ((int) Utils.getScreenPixelWidth(context) - context.getResources().getDimensionPixelSize(R.dimen._20sdp)) / 3;
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemTransactionTypeBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        TransactionTypesModel mapData = list_item.get(position);

        int vImage = mapData.getId();
        String vName = mapData.getName();
        boolean isSelected = mapData.getSelected();
        if (vImage != 0) {
            holder.binding.typeImgView.setVisibility(View.VISIBLE);
            new LoadImageGlide.builder(holder.itemView.getContext(), LoadImageGlide.bind(vImage), holder.binding.typeImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        } else {
            holder.binding.typeImgView.setVisibility(View.GONE);

        }

        holder.binding.typeTxt.setText(vName);

        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) holder.binding.typeContainerView.getLayoutParams();
        mParams.width = sWidth;
        holder.binding.typeContainerView.setLayoutParams(mParams);

        holder.binding.typeContainerView.setOnClickListener(v -> onItemClickList.onTypeClick(mapData));

        if (generalFunctions.isRTLmode()) {
            holder.binding.typeImgView.setScaleX(-1);
        }

        if (isSelected) {
            //holder.containerView.setBackground(getActContext().getResources().getDrawable(R.drawable.ic_square_shadow_type_selected));
            holder.binding.typeTxt.setTextColor(holder.itemView.getContext().getResources().getColor(R.color.appThemeColor_1));
            holder.binding.viewLine.setBackgroundColor(holder.itemView.getContext().getResources().getColor(R.color.appThemeColor_1));
        } else {
            //holder.containerView.setBackground(getActContext().getResources().getDrawable(R.drawable.ic_square_shadow_type_unseleted));
            holder.binding.typeTxt.setTextColor(holder.itemView.getContext().getResources().getColor(R.color.black));
            holder.binding.viewLine.setBackgroundColor(holder.itemView.getContext().getResources().getColor(R.color.cardView23ProBG));
        }

    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updatelist(ArrayList<TransactionTypesModel> updatedTypeList) {
        list_item.clear();
        list_item.addAll(updatedTypeList);
        notifyDataSetChanged();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemTransactionTypeBinding binding;

        private ViewHolder(ItemTransactionTypeBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public void setOnItemClickList(OnTypeClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnTypeClickList {
        void onTypeClick(TransactionTypesModel transactionTypesModel);
    }
}