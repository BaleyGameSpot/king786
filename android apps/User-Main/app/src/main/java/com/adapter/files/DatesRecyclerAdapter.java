package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemDatesDesignBinding;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;

public class DatesRecyclerAdapter extends RecyclerView.Adapter<DatesRecyclerAdapter.ViewHolder> {

    private final Context mContext;
    private final ArrayList<HashMap<String, Object>> listData;
    @Nullable
    private final OnDateSelectListener onDateSelectListener;
    private Date selectedDate;

    public DatesRecyclerAdapter(@NonNull Context mContext, @NonNull ArrayList<HashMap<String, Object>> listData, Date selectedDate, @Nullable OnDateSelectListener onDateSelectListener) {
        this.mContext = mContext;
        this.listData = listData;
        this.selectedDate = selectedDate;
        this.onDateSelectListener = onDateSelectListener;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void setSelectedDate(Date selectedDate) {
        this.selectedDate = selectedDate;
        this.notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemDatesDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull final ViewHolder holder, final int position) {

        final HashMap<String, Object> item = listData.get(position);
        final Date currentDate = (Date) listData.get(position).get("currentDate");

        if (item.get("dayNameTxt") instanceof String) {
            holder.binding.dayTxtView.setText((String) item.get("dayNameTxt"));
        }

        if (item.get("dayNumTxt") instanceof String) {
            holder.binding.dayNumTxtView.setText((String) item.get("dayNumTxt"));
        }

        if (selectedDate != null && selectedDate.equals(currentDate)) {
            holder.binding.dayTxtView.setTextColor(mContext.getResources().getColor(R.color.appThemeColor_TXT_1));
            holder.binding.dayNumTxtView.setTextColor(mContext.getResources().getColor(R.color.appThemeColor_TXT_1));
            holder.binding.cardview.setCardBackgroundColor(mContext.getResources().getColor(R.color.appThemeColor_1));
        } else {
            holder.binding.dayTxtView.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.commonText_Light));
            holder.binding.dayNumTxtView.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.text23Pro_Dark));
            holder.binding.cardview.setCardBackgroundColor(mContext.getResources().getColor(R.color.white));
        }

        holder.itemView.setOnClickListener(view -> {
            setSelectedDate(currentDate);
            if (onDateSelectListener != null) {
                onDateSelectListener.onDateSelect(position, true);
            }
        });
        if (!Utils.checkText(holder.binding.dayNumTxtView.getText().toString())) {
            holder.binding.dayNumTxtView.setVisibility(View.GONE);
        } else {
            holder.binding.dayNumTxtView.setVisibility(View.VISIBLE);
        }

    }

    @Override
    public int getItemCount() {
        return listData.size();
    }

    public interface OnDateSelectListener {
        void onDateSelect(int position, boolean isNewData);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemDatesDesignBinding binding;

        private ViewHolder(ItemDatesDesignBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}
