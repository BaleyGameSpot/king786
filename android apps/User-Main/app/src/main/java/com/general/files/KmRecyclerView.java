package com.general.files;

import android.content.Context;
import android.graphics.Canvas;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.utils.MyUtils;

public class KmRecyclerView extends RecyclerView {
    public KmRecyclerView(@NonNull Context context) {
        super(context);
    }

    public KmRecyclerView(@NonNull Context context, @Nullable AttributeSet attrs) {
        super(context, attrs);
    }

    public KmRecyclerView(@NonNull Context context, @Nullable AttributeSet attrs, int defStyle) {
        super(context, attrs, defStyle);
    }

    @Override
    public void setAdapter(@Nullable Adapter adapter) {
        super.setAdapter(adapter);
        if (getAdapter() instanceof KmStickyListener listener) {
            this.addItemDecoration(new KmHeaderItemDecoration(listener));
        }
    }

    private static class KmHeaderItemDecoration extends ItemDecoration {

        private final KmStickyListener mListener;
        private int mHeaderHeight;

        public KmHeaderItemDecoration(KmStickyListener listener) {
            this.mListener = listener;
        }

        @Override
        public void onDrawOver(@NonNull Canvas c, @NonNull RecyclerView parent, @NonNull RecyclerView.State state) {
            super.onDrawOver(c, parent, state);
            View topChild = parent.getChildAt(0);
            if (topChild == null) {
                return;
            }

            int topChildPosition = parent.getChildAdapterPosition(topChild);
            if (topChildPosition == RecyclerView.NO_POSITION) {
                return;
            }

            int headerPos = mListener.getHeaderPositionForItem(topChildPosition);
            View currentHeader = getHeaderViewForItem(headerPos, parent);
            fixLayoutSize(parent, currentHeader);
            int contactPoint = currentHeader.getBottom();
            View childInContact = getChildInContact(parent, contactPoint, headerPos);

            if (childInContact != null && mListener.isHeader(parent.getChildAdapterPosition(childInContact))) {
                moveHeader(c, currentHeader, childInContact);
                return;
            }

            drawHeader(c, currentHeader);
        }

        private View getHeaderViewForItem(int headerPosition, RecyclerView parent) {
            int layoutResId = mListener.getHeaderLayout(headerPosition);
            View header = LayoutInflater.from(parent.getContext()).inflate(layoutResId, parent, false);
            header.setLayoutDirection(MyApp.getInstance().getAppLevelGeneralFunc().isRTLmode() ? View.LAYOUT_DIRECTION_RTL : LAYOUT_DIRECTION_LTR);
            mListener.bindHeaderData(header, headerPosition);
            return header;
        }

        private void drawHeader(Canvas c, View header) {
            c.save();
            c.translate(0, 0);
            header.draw(c);
            c.restore();
        }

        private void moveHeader(Canvas c, View currentHeader, View nextHeader) {
            c.save();
            c.translate(0, nextHeader.getTop() - (float) currentHeader.getHeight());
            currentHeader.draw(c);
            c.restore();
        }

        private View getChildInContact(RecyclerView parent, int contactPoint, int currentHeaderPos) {
            View childInContact = null;
            for (int i = 0; i < parent.getChildCount(); i++) {
                int heightTolerance = 0;
                View child = parent.getChildAt(i);

                //measure height tolerance with child if child is another header
                if (currentHeaderPos != i) {
                    boolean isChildHeader = mListener.isHeader(parent.getChildAdapterPosition(child));
                    if (isChildHeader) {
                        heightTolerance = mHeaderHeight - child.getHeight();
                    }
                }

                //add heightTolerance if child top be in display area
                int childBottomPosition;
                if (child.getTop() > 0) {
                    childBottomPosition = child.getBottom() + heightTolerance;
                } else {
                    childBottomPosition = child.getBottom();
                }

                if (childBottomPosition > contactPoint) {
                    if (child.getTop() <= contactPoint) {
                        // This child overlaps the contactPoint
                        childInContact = child;
                        break;
                    }
                }
            }
            return childInContact;
        }

        private void fixLayoutSize(ViewGroup parent, View view) {

            // Specs for parent (RecyclerView)
            int widthSpec = View.MeasureSpec.makeMeasureSpec(parent.getWidth(), View.MeasureSpec.EXACTLY);
            int heightSpec = View.MeasureSpec.makeMeasureSpec(parent.getHeight(), View.MeasureSpec.UNSPECIFIED);

            // Specs for children (headers)
            int childWidthSpec = ViewGroup.getChildMeasureSpec(widthSpec, parent.getPaddingLeft() + parent.getPaddingRight(), view.getLayoutParams().width);
            int childHeightSpec = ViewGroup.getChildMeasureSpec(heightSpec, parent.getPaddingTop() + parent.getPaddingBottom(), view.getLayoutParams().height);

            view.measure(childWidthSpec, childHeightSpec);
            if (MyUtils.isShadow) {
                view.setBackgroundResource(R.drawable.custom_shadow_header);
            }

            view.layout(0, 0, view.getMeasuredWidth(), mHeaderHeight = view.getMeasuredHeight());
        }
    }

    public interface KmStickyListener {
        int getHeaderPositionForItem(int itemPosition);

        int getHeaderLayout(int headerPosition);

        void bindHeaderData(View header, int headerPosition);

        boolean isHeader(int itemPosition);
    }
}