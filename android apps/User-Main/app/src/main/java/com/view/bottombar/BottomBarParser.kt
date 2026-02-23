package com.view.bottombar

import android.content.Context
import android.content.res.Resources
import android.content.res.XmlResourceParser
import android.graphics.RectF
import android.graphics.drawable.Drawable
import androidx.annotation.XmlRes
import androidx.core.content.ContextCompat

internal class BottomBarParser() {

    var mContext: Context ? = null
    private var parser: XmlResourceParser ? = null
    var bar_items: ArrayList<BottomBarItem>? = null

    var menu_res: Int = -1

    constructor(context: Context, @XmlRes res: Int) : this() {
        this.mContext = context
        this.menu_res = res

        parser = mContext!!.resources.getXml(menu_res)
    }

    constructor(context: Context, res: ArrayList<BottomBarItem>) : this() {
        this.mContext = context
        this.bar_items = res
    }

    fun parse(): MutableList<BottomBarItem> {
        val items: MutableList<BottomBarItem> = mutableListOf()

        if (bar_items != null && bar_items!!.isNotEmpty()) {

            for (barItem in bar_items!!) {
                if (barItem.icon == null) {
                    throw Throwable("Item icon can not be null!")
                }

                items.add(
                    BottomBarItem(
                        barItem.title.toString(),
                        if (barItem.contentDescription == null || barItem.contentDescription!!.isEmpty()) barItem.title.toString() else barItem.contentDescription.toString(),
                        barItem.icon,
                        RectF(),
                        alpha = 0
                    )
                )
            }

            return items
        }

        var eventType: Int?

        do {
            eventType = parser!!.next()
            if (eventType == XmlResourceParser.START_TAG && parser!!.name == ITEM_TAG) {
                items.add(getTabConfig(parser!!))
            }
        } while (eventType != XmlResourceParser.END_DOCUMENT)

        return items
    }

    private fun getTabConfig(parser: XmlResourceParser): BottomBarItem {
        val attributeCount = parser.attributeCount
        var itemText: String? = null
        var itemDrawable: Drawable? = null
        var contentDescription: String? = null

        for (index in 0 until attributeCount) {
            when (parser.getAttributeName(index)) {
                ICON_ATTRIBUTE -> itemDrawable = ContextCompat.getDrawable(
                    mContext!!,
                    parser.getAttributeResourceValue(index, 0)
                )

                TITLE_ATTRIBUTE -> itemText = try {
                    mContext!!.getString(parser.getAttributeResourceValue(index, 0))
                } catch (notFoundException: Resources.NotFoundException) {
                    parser.getAttributeValue(index)
                }

                CONTENT_DESCRIPTION_ATTRIBUTE -> contentDescription = try {
                    mContext!!.getString(parser.getAttributeResourceValue(index, 0))
                } catch (notFoundException: Resources.NotFoundException) {
                    parser.getAttributeValue(index)
                }
            }
        }

        if (itemDrawable == null) {
            throw Throwable("Item icon can not be null!")
        }

        return BottomBarItem(
            itemText.toString(),
            contentDescription ?: itemText.toString(),
            itemDrawable,
            alpha = 0
        )
    }

    companion object {
        private const val ITEM_TAG = "item"
        private const val ICON_ATTRIBUTE = "icon"
        private const val TITLE_ATTRIBUTE = "title"
        private const val CONTENT_DESCRIPTION_ATTRIBUTE = "contentDescription"
    }
}
