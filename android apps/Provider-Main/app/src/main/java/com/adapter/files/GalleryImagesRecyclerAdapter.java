package com.adapter.files;

import android.content.Context;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.appcompat.content.res.AppCompatResources;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.utils.VectorUtils;

import java.util.ArrayList;
import java.util.HashMap;

public class GalleryImagesRecyclerAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;
    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;

    int itemWidth;

    public GalleryImagesRecyclerAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
        itemWidth = (int) (Utils.getScreenPixelWidth(mContext) / 3) - mContext.getResources().getDimensionPixelSize(R.dimen._20sdp);
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        if (viewType == TYPE_FOOTER) {
            View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false);
            this.footerView = v;
            return new FooterViewHolder(v);
        } else {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_gallery_list, parent, false);
            return new ViewHolder(view);
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {


        if (holder instanceof ViewHolder) {
            final HashMap<String, String> item = list.get(position);

            final ViewHolder viewHolder = (ViewHolder) holder;
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) viewHolder.contentAreaView.getLayoutParams();

            params.width = itemWidth;
            params.height = itemWidth;

            viewHolder.contentAreaView.setLayoutParams(params);

            viewHolder.deleteImgView.setVisibility(View.GONE);

            if (generalFunc.isRTLmode()) {
                viewHolder.deleteImgView.setScaleX(-1);
            }
            viewHolder.deleteImgView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onDeleteClick(view, position);
                }
            });

            viewHolder.seperatorView.setVisibility(View.GONE);
            if (position == list.size() - 1) {
                viewHolder.seperatorView.setVisibility(View.VISIBLE);
            }

            if (item.get("eFileType").equals("Image")) {
                if (!TextUtils.isEmpty(item.get("vImage"))) {
                    String imageUrl = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), item.get("vImage"), params.width, params.height);

                    new LoadImageGlide.builder(mContext, LoadImageGlide.bind(imageUrl), viewHolder.galleryImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

                }
                viewHolder.deleteImgView.setVisibility(View.VISIBLE);
                viewHolder.exoPlay.setVisibility(View.GONE);
            } else if (item.get("eFileType").equals("Video")) {

                if (!TextUtils.isEmpty(item.get("ThumbImage"))) {
                    String imageUrl = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), item.get("ThumbImage"), params.width, params.height);

                    new LoadImageGlide.builder(mContext, LoadImageGlide.bind(imageUrl), viewHolder.galleryImgView).setErrorImagePath(R.drawable.ic_novideo__icon).setPlaceholderImagePath(R.drawable.ic_novideo__icon).build();

                } else {
                    viewHolder.galleryImgView.setImageDrawable(AppCompatResources.getDrawable(mContext, R.drawable.ic_novideo__icon));
                }
                viewHolder.deleteImgView.setVisibility(View.VISIBLE);
                VectorUtils.manageVectorImage(mContext, viewHolder.exoPlay, R.drawable.ic_play_video, R.drawable.ic_play_video_compat);
                viewHolder.exoPlay.setVisibility(View.VISIBLE);
            }


            viewHolder.galleryImgView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(view, position);
                }
            });
        } else {
            FooterViewHolder footerHolder = (FooterViewHolder) holder;
            this.footerHolder = footerHolder;
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled == true) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled) {
            return list.size() + 1;
        } else {
            return list.size();
        }
    }

    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null) {
            footerHolder.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressContainer.setVisibility(View.GONE);
    }


    public interface OnItemClickListener {
        void onItemClickList(View v, int position);

        void onDeleteClick(View v, int position);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {

        public AppCompatImageView galleryImgView;
        public AppCompatImageView deleteImgView;

        public View contentView;
        public View contentAreaView;
        public View seperatorView;
        public ImageView exoPlay;

        public ViewHolder(View view) {
            super(view);
            contentView = view;
            seperatorView = view.findViewById(R.id.seperatorView);
            contentAreaView = view.findViewById(R.id.contentAreaView);
            galleryImgView = (AppCompatImageView) view.findViewById(R.id.galleryImgView);
            deleteImgView = (AppCompatImageView) view.findViewById(R.id.deleteImgView);
            exoPlay = view.findViewById(R.id.exoPlay);

        }
    }

    class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressContainer;

        public FooterViewHolder(View itemView) {
            super(itemView);

            progressContainer = (LinearLayout) itemView.findViewById(R.id.progressContainer);

        }
    }
}
