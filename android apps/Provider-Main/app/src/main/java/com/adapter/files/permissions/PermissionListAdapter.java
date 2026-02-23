package com.adapter.files.permissions;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.providers.R;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class PermissionListAdapter extends RecyclerView.Adapter<PermissionListAdapter.ViewHolder> {

    private final ArrayList<HashMap<String, String>> mList;

    public PermissionListAdapter(ArrayList<HashMap<String, String>> list) {
        this.mList = list;
    }

    @NonNull
    @Override
    public PermissionListAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_permission, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(PermissionListAdapter.ViewHolder holder, final int position) {

        HashMap<String, String> item = mList.get(position);
        holder.addressText.setText(item.get("title"));
        holder.subAddressText.setText(item.get("subTitle"));
        if (item.containsKey("icon")) {
            holder.ivIcon.setImageResource(Integer.parseInt(item.get("icon")));
        }
    }

    @Override
    public int getItemCount() {
        return mList.size();
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        private final ImageView ivIcon;
        private final MTextView addressText, subAddressText;

        public ViewHolder(View itemView) {
            super(itemView);
            ivIcon = itemView.findViewById(R.id.ivIcon);
            addressText = itemView.findViewById(R.id.txtTitle);
            subAddressText = itemView.findViewById(R.id.txtSubTitle);
        }
    }
}